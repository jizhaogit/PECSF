<?php

namespace App\Console\Commands;

use stdClass;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\ScheduleJobAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ExportDatabaseToBI extends Command
{

    protected $db_tables = [
        ['name' => 'campaign_years',     'delta' => 'updated_at', 'hidden' => null ],
        ['name' => 'charities',          'delta' => 'updated_at', 'hidden' => null ],
        ['name' => 'f_s_pools',          'delta' => 'updated_at', 'hidden' => null ],
        ['name' => 'f_s_pool_charities', 'delta' => 'updated_at', 'hidden' => ['image'] ],
        ['name' => 'organizations',      'delta' => 'updated_at', 'hidden' => null ],
        ['name' => 'pledge_charities',   'delta' => 'updated_at', 'hidden' => null ],
        ['name' => 'pledges',            'delta' => 'updated_at', 'hidden' => null ],
        ['name' => 'regions',            'delta' => 'updated_at', 'hidden' => null ],
        ['name' => 'users',              'delta' => 'updated_at', 'hidden' => ['password', 'remember_token'] ],
        ['name' => 'volunteers',         'delta' => 'updated_at', 'hidden' => null ],
    ];
 
    protected $success;
    protected $failure;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ExportDatabaseToBI';  

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Greenfield database to Datawarehouse vis ODS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        $this->info( now() );

        // Main Loop
        foreach ($this->db_tables as $table) {
           $table_name =  $table['name'];
           $delta_field = $table['delta'];
           $hidden_fields = $table['hidden'];

           $this->sendTableDataToDataWarehouse($table_name, $delta_field, $hidden_fields);

           $data  = DB::table($table_name)->get()->toJson();

        }

    

        return 0;
    }


    /**
     * Main Function for sending pledges transactions to Datawarehouse.
     *
     * @return int
     */
    private function sendTableDataToDataWarehouse($table_name, $delta_field, $hidden_fields) {
        $this->info("Table '{$table_name}' Detail to BI (Datawarehouse) start");

        $this->success = 0;
        $this->failure = 0;
        $n = 0;
        
        // Create the Task Audit log
        $job_name = $this->signature . ':'. $table_name;
        $task = ScheduleJobAudit::Create([
            'job_name' => $job_name,
            'start_time' => Carbon::now(),
            'status','Initiated'
        ]);

        // Get the latest success job 
        $last_job = ScheduleJobAudit::where('job_name', $job_name)
                      ->where('status','Completed')
                      ->orderBy('end_time', 'desc')->first();


        $last_start_time = $last_job ? $last_job->start_time : '2000-01-01' ; 

        // Main Process for each table 
        $sql = DB::table($table_name)
            ->when( $last_job && $delta_field, function($q) use($last_start_time, $delta_field, $hidden_fields) {
                return $q->where($delta_field, '>=', $last_start_time);
            })
            ->orderBy('id');
        
        // Chucking
        $sql->chunk(5000, function($chuck) use($table_name, $hidden_fields, $last_job, &$n) {
            $this->info( "Sending table '{$table_name}' batch (5000) - " . ++$n );

            //$chuck->makeHidden(['password', 'remember_token']);
            if ($hidden_fields) {
                foreach($chuck as $item) {
                    foreach($hidden_fields as $hidden_field) {
                        // unset($item->password);
                        unset($item->$hidden_field);
                    }
                }
            }

            $pushdata = new stdClass();
            $pushdata->table_name = $table_name;
            $pushdata->table_data = json_encode($chuck);
            $pushdata->delta_ind = $last_job ? "1" : "0";

            $this->sendData( $pushdata );
            
            unset($pushdata);
        });


        $this->info("Table '{$table_name}' data sent completed");
        $this->info( now() );
        $this->info("Success - " . $this->success);
        $this->info("failure - " . $this->failure);

        // Update the Task Audit log
        $task->end_time = Carbon::now();
        $task->status = 'Completed';
        $task->save();

        return 0;

    }

    
    protected function sendData($pushdata) {

        $response = Http::withBasicAuth(
            env('ODS_USERNAME'),
            env('ODS_TOKEN')
        )->withBody( json_encode($pushdata), 'application/json')
        ->post( env('ODS_OUTBOUND_BULK_UPLOAD_BI_ENDPOINT') );

        if ($response->successful()) {
            $this->success += 1;
        } else {
                                    
            $this->info( $response->status() );
            $this->info( $response->body() );
            // dd( json_encode($data) );
            //$this->info( "Failed : " . print_r($response) );
            $this->failure += 1;
        }

    }

}
