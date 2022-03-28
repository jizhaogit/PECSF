@extends('donate.layout.main')

@section ("content")

<div class="container">
    <form action="{{route('donate.save.summary')}}" method="POST">
    <div class="row">
        <div class="col-12 col-sm-7">
            <h2 class="mt-5">4. Summary</h2>
            <p class="mt-3">Please review your donation plan and press <b>Pledge</b> when ready!</p>
                <div class="card bg-light p-3">
                    <p class="card-title"><b>Deductions</b></p>
                    <div class="card">
                        <div class="card-body">
                            <span><b>Your Bi-weekly payroll deductions:</b></span>
                            <span class="float-right mb-2">${{ $calculatedTotalAmountBiWeekly }}</span><br>
                            <h6>AND / OR</h6>
                            <span><b>Your One-time payroll deductions:</b></span>
                            <span class="float-right">${{ $calculatedTotalAmountOneTime }}</span>
                            <hr>
                            <p class="text-right"><b>Total :</b> ${{ $grandTotal }}</p>
                        </div>
                    </div>
                    @csrf
                    @foreach(['one-time', 'bi-weekly'] as $key)
                        @if($key === 'one-time' && (session()->get('amount-step')['frequency'] === 'one-time' || session()->get('amount-step')['frequency'] === 'both'))
                            @php $key_ = $key; @endphp
                            @php $keyCase = 'oneTime'; @endphp
                            @include('donate.partials.summary-distribution')
                        @endif
                        @if($key === 'bi-weekly' && (session()->get('amount-step')['frequency'] === 'bi-weekly' || session()->get('amount-step')['frequency'] === 'both'))
                            @php $key_ = $key;@endphp
                            @php $keyCase = 'biWeekly'; @endphp
                            @include('donate.partials.summary-distribution')
                        @endif
                    @endforeach
                </div>
                <div class="row">
                    <p class="py-4">
                    Please note that <b>this is not a tax receipt</b>.  
                    Payroll Deductions begin with the first paycheque in January and will appear on your T4 issued from payroll from that calendar year. PECSF issues cheques twice a year. 
                    In August for payroll deductions from January -June, and in March for payroll deductions from July -December.
                    </p>
                </div>
                <div class="">
                    @foreach ($charities as $charity)
                        <input type="hidden" name="charityOneTimeAmount[{{$charity['id']}}]" value="{{$charity['one-time-amount-distribution']}}">
                        <input type="hidden" name="charityBiWeeklyAmount[{{$charity['id']}}]" value="{{$charity['bi-weekly-amount-distribution']}}">
                        <input type="hidden" name="charityAdditional[{{$charity['id']}}]" value="{{$charity['additional']}}">
                        <input type="hidden" name="annualOneTimeAmount" value="{{$annualOneTimeAmount}}">
                        <input type="hidden" name="annualBiWeeklyAmount" value="{{$annualBiWeeklyAmount}}">
                        <input type="hidden" name="frequency" value="{{$frequency}}">
                    @endforeach
                </div>

        </div>
        <div class="col-12 col-sm-5 text-center">
            <img src="{{ asset('img/donor.png') }}" alt="Donor" class="py-5 img-fluid">
             <p>
                Making changes to your pledge outside of Campaign time? Please contact <a href="mailto:PECSF@gov.bc.ca" class="text-primary">PECSF@gov.bc.ca</a> directly or submit an <a href="https://www2.gov.bc.ca/gov/content/careers-myhr" class="text-primary" target="_blank">AskMyHR</a> service request to make any changes on your existing pledge outside the annual campaign/open enrollment period (September-December). 
            </p>
            <p><b>Questions?</b> <a href="https://www.gov.bc.ca/pecsf" class="text-primary" target="_blank">www.gov.bc.ca/pecsf</a>         <b>Email:</b> <a href="mailto:PECSF@gov.bc.ca" class="text-primary">PECSF@gov.bc.ca</a></p>
        </div>
    </div>
    <div class="row">
        <div class="col">
           
            <div>
                <strong>Freedom of Information and Protection of Privacy Act</strong>
                <p class="py-3">Personal information on this form is collected by the BC Public Service Agency for the purposes of processing and reporting on your pledge for charitable contributions to the Community Fund under section 26(c) of the Freedom of Information and Protection of Privacy Act.</p>
                <p>By clicking the Submit button, I hereby consent to the disclosure, within Canada only, by the BC Public Service Agency of my name to my Organization's Lead PECSF Coordinator for the purpose of administering the Ministry's participation incentive draws in the current Campaign. This consent is effective until such time as my consent is revoked by me in writing to the Campaign Manager, PECSF.</p>
                <p>Questions about the collection of your personal information can be directed to the Campaign Manager, Provincial Employees Community Services Fund at 250 356-1736, <a href="mailto:PECSF@gov.bc.ca" class="text-primary">PECSF@gov.bc.ca</a> or PO Box 9564 Stn Prov Govt, Victoria, BC V8W 9C5</p>
            </div>
            <a class="btn btn-lg btn-outline-primary" href="{{route('donate.distribution')}}">Previous</a>
            <button class="btn btn-lg btn-primary" type="submit">Pledge</button>
        </div>
    </div>
    </form>

</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/custom-switch.css') }}">
@endpush
@push('js')
    <script>
        $(document).on('change', '#distributeByDollarAmountOneTime, #distributeByDollarAmountBiWeekly', function () {
            const frequency = $(this).attr('id') === 'distributeByDollarAmountOneTime' ? '#oneTimeSection' : '#biWeeklySection';
            if (!$(this).prop("checked")) {
                $(frequency).find(".by-amount").removeClass("d-none");
                $(frequency).find(".by-percent").addClass("d-none"); 
                $(frequency).find(".percent-amount-text").html("Distribute by Percentage");
            } else {
                $(frequency).find(".by-percent").removeClass("d-none");
                $(frequency).find(".by-amount").addClass("d-none");
                $(frequency).find(".percent-amount-text").html("Distribute by Dollar Amount");
            }
        });
        $(document).on('change', '.percent-input', function () {
            let total = 0;
            const section = $(this).parents('.amountDistributionSection');
            section.find(".percent-input").each( function () {
                total += Number($(this).val());
            });
            section.find(".total-percent").val(total);
            section.find(".total-percent-text").text('Total Amount: '+total + '%');
        });


        $(document).on('change', '.amount-input', function () {
            let total = 0;
            const section = $(this).parents('.amountDistributionSection');

            const expectedTotal = section.find(".total-amount").data('expected-total');
            section.find(".amount-input").each( function () {
                total += Number($(this).val());
            });
            section.find(".total-amount").val(total);
        });

        $(".percent-input").change();
        $(".amount-input").change();

    </script>
@endpush