<script>
    var name = $("input[name=name]");
    var email = $("input[name=email]");
    var nationality = $("select[name=nationality]");
    var passport = $("input[name=passport]");
    var passport_expiry_date = $("input[name=passport_expiry_date]");
    var dob = $("input[name=dob]");
    var mobile = $("input[name=mobile]");

    function showBirthCert(){

        $("#birth_cert_div").show();
    }
    function hideBirthCert(){
        $("#birth_cert_div").hide();
    }
    $("select[name=relation_ship]").on("change",function (e) {
        if($(this).val() == 'child' && $(this).data('child') == '1')
            showBirthCert();
        else
            hideBirthCert();

        if(typeof show_parent != 'undefined' && show_parent && $(this).val() == 'parent'){
            $("#ask_parent").show();
        }else{
            $("#ask_parent").hide();
        }
    })

    $(document).ready(function () {
        $("select[name=relation_ship]").trigger("change");
        @if(!in_array('mobile',$hide ?? []))
        $("input[name='mobile']").inputmask("099-99999999",{placeholder:" ", clearMaskOnLostFocus: true });
        @endif
        changeNric($("select[name=nationality]").val());
        @if(in_array('occ',$show ?? []))
            $.get("{{route('wb-api.initPostRegisterIndividual')}}",{},function (res) {
            if(res.status == 'success'){

                var industries = '<option value="">Please Select ...</option>';
                res.data.industries.map((val,i)=>{
                    industries += '<option value="'+val.uuid+'">'+val.name+'</option>';
                })
                $("select[name=industry]").html(industries);
                if($("select[name=industry]").data('value') != '') {
                    val = $("select[name=industry]").data('value');
                    $("select[name=industry]").attr('data-value', '');
                    $("select[name=industry]").val(val).change();
                }else{
                    $(".loading").hide();
                }

            }
        })
        @endif

    });
    @if(in_array('occ',$show ?? []))
        $("select[name=industry]").on("change",function(e){
        if($(this).val() =='' || $(this).val() ==null)
            return;
        $.get("{{route('wb-api.getIndustryJobsList')}}",{industry:$(this).val(),gender:$("input[name=gender]").val()},function(res){
            $(".loading").hide();
            if(res.status == 'success'){
                var jobs = '<option value="">Please Select ...</option>';
                res.data.map((val,i)=>{
                    jobs += '<option value="'+val.uuid+'">'+val.name+'</option>';
                })
                $("select[name=job]").html(jobs);
                if($("select[name=job]").data('value') != '') {
                    var val = $("select[name=job]").data('value');
                    $("select[name=job]").attr('data-value','');
                    $("select[name=job]").val(val).change();

                }else{
                    $(".loading").hide();
                }
                if(res.data.length == 1){
                    console.log(res.data[0].id);
                    $("select[name=job]").val(res.data[0].id).change();
                }

            }
        })
    })
    @endif

    @if(in_array('personal_income',$show ?? []) || in_array('household_income',$show ?? []))
        var personal = $("input[name=personal_income]").val();


        var household = $("input[name=household_income]").val();


        var household_income_slider = document.getElementById('household_income_slider');
        noUiSlider.create(household_income_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 10001
            }
        });

        var personal_income_slider = document.getElementById('personal_income_slider');
        noUiSlider.create(personal_income_slider, {
            start: 0,
            connect: [true, false],
            behaviour: 'drag',
            range: {
                'min': 0,
                'max': 10001
            }
        });

        household_income_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            $("#household_income_value").html(value == 10001 ? '10k+' : value);
            $("input[name=household_income]").val(value);

            var personal = $("input[name=personal_income]").val();
            if(personal > value){
                personal_income_slider.noUiSlider.set([value]);
            }
        });
        personal_income_slider.noUiSlider.on('update', function (values, handle) {
            var value = Math.round(values[0]);
            $("#personal_income_value").html(value == 10001 ? '10k+' : value);
            $("input[name=personal_income]").val(value);

            var household = $("input[name=household_income]").val();
            if(household < value){
                household_income_slider.noUiSlider.set([value]);
            }
        });
        personal_income_slider.noUiSlider.set([personal]);
        household_income_slider.noUiSlider.set([household]);
    @endif

    <?php     
    $dob_min = \Carbon\Carbon::now()->subYears(65);
    $dob_max = \Carbon\Carbon::now()->subDays(14);

    $ped_min = \Carbon\Carbon::now()->addMonth(1);
    $ped_max = \Carbon\Carbon::now()->addYears(20);
    ?>
    $('.ped').pickadate({
        selectYears: true,
        selectMonths: true,
        format: 'dd/mm/yyyy',
        selectYears: 100,
        max: [{{$ped_max->format('Y')}},{{$ped_max->format('m') - 1}},{{$ped_max->format('d')}}],
    min: [{{$ped_min->format('Y')}},{{$ped_min->format('m') - 1}},{{$ped_min->format('d')}}]
    });
    $('.dob').pickadate({
        selectYears: true,
        selectMonths: true,
        format: 'dd/mm/yyyy',
        selectYears: 100,
        max: [{{$dob_max->format('Y')}},{{$dob_max->format('m') - 1}},{{$dob_max->format('d')}}],
    min: [{{$dob_min->format('Y')}},{{$dob_min->format('m') - 1}},{{$dob_min->format('d')}}]
    });

    function mykadDate(value){
        var mykad = replaceAll(value,"-","");
        mykad = replaceAll(mykad,"_","");
        if(mykad.length != 12)
            return  false;
        var year = mykad.substr(0,2);
        var month = mykad.substr(2,2);
        var day = mykad.substr(4,2);
        var gender = (((parseInt(mykad.substr(11,1))) % 2 == 0) ? 'female' : 'male');

        var nowYear = new Date().getFullYear().toString().substr(2,2);

        var nowDate = new Date();
        var date = new Date("20"+year,month -1,day);
        var decade = '20';
        if(date > nowDate)
            decade = '19';

        year = decade + year;
        if(parseInt(year) < 1900 || parseInt(year) > parseInt(new Date().getFullYear()))
            return false;
        if(day < 1 || day > 31)
            return false;
        if(month < 1 || month > 12)
            return false;

        {{--var max_year = {{now()->subYears(16)->format("Y")}};--}}
        {{--if(year >= max_year)--}}
        {{--    return  false;--}}

        return  day+'/'+month+'/'+year;
    }
    function isLocal(val) {
        return val == '{{\App\Country::where("nationality","Malaysian")->first()->uuid}}'
    }
    function changeNric(value){
        if(isLocal(value)) {

            $("input[name='passport_expiry_date']").parents('.form-group').hide();
            $("#mykad_passport").html("{{__('web/profile.mykad')}}");
            $("input[name=passport]").attr("placeholder","{{__('web/profile.mykad')}}");
            $("input[name='passport']").inputmask("999999-99-9999");
        }else {

            $("input[name='passport_expiry_date']").parents('.form-group').show();
            $("#mykad_passport").html("{{__('web/profile.passport')}}");
            $("input[name=passport]").attr("placeholder","{{__('web/profile.passport')}}");
            $("input[name='passport']").inputmask('*{1,20}');

        }
    };
    $("select[name=nationality]").on("change",function (e) {
        changeNric($("select[name=nationality]").val());
    });
    $("input[name=dob]").on("change",function (e) {
        console.log("changed");

        var passport = $("input[name=passport]");
        if(!$(".modal").hasClass("show"))
            return;

        var nationality = $("select[name=nationality]");
        if(passport.val() != '' && isLocal(nationality.val()) && $(this).val() != mykadDate(passport.val())){
            Swal.fire({
                title: '{{__('web/product.change_date')}}',
                text: '{{__('web/product.change_date_desc')}}',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-danger ml-1',
                buttonsStyling: false,
            }).then(function (result) {
                if (result.value) {

                } else {
                    parseMykad(passport.val())
                }

            })
        }
    });
    $("input[name=passport]").on("keyup",function (e) {
        var value = $(this).val();
        parseMykad(value);
    });
    $(".gender-selector").on("click",function(e){
        $(".gender-selector").removeClass('selected');
        $(this).addClass('selected');
        if($(this).data('value') == 'male')
            $('input[name=gender]').val('Male').change();
        else
            $('input[name=gender]').val('Female').change();

    })
</script>
