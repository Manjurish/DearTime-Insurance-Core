
<div class="add-new-data-sidebar">
    <div class="modal fade text-left" id="add-nominee-modal"  role="dialog" data-backdrop="false"
         aria-labelledby="myModalLabel160" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary white">
                    <h5 class="modal-title" id="myModalLabel160">{{$title ?? __('web/beneficiary.new_nominee')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="add-nominee-form" method="post">
                    <div class="modal-body">


                        <div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{__('web/beneficiary.full_name')}}</label>

                                    <input type="text" class="form-control required" name="name" value=""
                                           placeholder="{{__('web/beneficiary.full_name')}}">
                                </div>
                            </div>
                            @if(!in_array('email',$hide ?? []))
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{__('web/beneficiary.email')}}</label>

                                    <input type="text" class="form-control required" name="email" value=""
                                           placeholder="{{__('web/beneficiary.email')}}">
                                </div>
                            </div>
                            @endif
                            @if(!in_array('mobile',$hide ?? []))
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>{{__('web/beneficiary.mobile')}}</label>

                                        <input type="text" class="form-control required" name="mobile" value=""
                                               placeholder="{{__('web/beneficiary.mobile')}}">
                                    </div>
                                </div>
                            @endif
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{__('web/profile.nationality')}}</label>

                                    <select class="form-control required select2" name="nationality">
                                        @foreach(\App\Country::get() as $region)
                                            <option @if($region->nationality == 'Malaysian') selected @endif value="{{$region->uuid}}">{{$region->nationality}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label id="mykad_passport">{{__('web/profile.mykad_passport')}}</label>

                                    <input type="text" class="form-control required" name="passport" value="">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{__('web/profile.passport_expiry_date')}}</label>

                                    <input type="text" class="form-control required ped" name="passport_expiry_date"
                                           value="" placeholder="{{__('web/profile.passport_expiry_date')}}">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label>{{__('web/profile.dob')}}</label>

                                    <input type="text" class="form-control required dob" name="dob" value=""
                                           placeholder="{{__('web/profile.dob')}}">
                                </div>
                            </div>
                            <div class="col-12">
                                <style>
                                    .gender-selector {
                                        border: 1px solid transparent;
                                        border-radius: 20px;
                                        width: 110px;
                                        height: 110px;
                                        margin: 10px;
                                    }

                                    .selected {
                                        box-shadow: 0px 5px 30px rgba(0, 0, 0, 0.07);
                                        border: 1px solid #ccc !important;
                                        transition: all 0.5s ease-in-out;
                                    }
                                    .selected path {
                                        stroke : #000
                                    }

                                </style>

                                <div class="form-group">
                                    <p class="mb-1">{{__('web/profile.gender')}}</p>

                                    <input type="hidden" name="gender" value="Male">
                                    <div class="row mb-2  d-flex justify-content-center align-items-center">
                                        <div data-value="male"
                                             class="gender-selector selected  p-25 d-flex align-items-center justify-content-center flex-column">
                                            <svg width="43" height="50" viewBox="0 0 43 50" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M29.9771 14.9593C29.9771 14.9593 31.0541 13.921 31.9654 15.211C33.5586 17.4529 29.8177 20.497 29.8177 20.497M11.7481 14.4287C11.7481 14.4287 10.3497 7.80937 12.6126 4.44462C14.6575 1.39951 18.6992 1.00716 20.5806 1.0001C21.2516 0.997581 21.9231 0.999444 22.5941 1.00001C24.5306 1.00163 28.7666 1.3775 30.8263 4.44462C33.0893 7.80937 31.6908 14.4287 31.6908 14.4287M13.5551 14.9593C13.5551 14.9593 12.4781 13.921 11.5668 15.211C9.97358 17.4529 13.7144 20.497 13.7144 20.497M15.1891 30.9558C15.1891 30.9558 5.76444 32.0491 3.79814 34.2697C1.67928 36.6598 -0.736221 42.2451 2.78956 45.5929C6.38315 49 21.5881 49 21.5881 49C21.5881 49 21.5881 49 21.5881 49C21.5881 49 37.0642 49 40.6578 45.5929C44.1836 42.2451 41.7596 36.6682 39.6492 34.2697C37.6829 32.0491 28.2583 30.9558 28.2583 30.9558M15.1891 30.9558C17.1046 30.6676 17.9521 26.8536 17.9521 26.8536M15.1891 30.9558C14.2653 31.7694 12.9347 32.0575 15.8756 35.8376C17.9769 38.5383 19.9873 39.4003 20.964 39.6724C21.3678 39.7849 21.7904 39.7857 22.1971 39.6841C23.2331 39.4252 25.4185 38.5772 27.5548 35.8376C28.2705 34.9198 28.7332 34.2082 29.0104 33.6451C29.9294 31.7779 26.698 30.9081 25.9701 28.9585C25.58 27.9138 25.58 26.8536 25.58 26.8536M14.8671 23.4975C16.7062 28.2861 21.588 28.2861 21.5881 28.2861C21.5881 28.2861 21.5881 28.2861 21.5881 28.2861C21.5881 28.2861 26.7327 28.2861 28.5803 23.4975C29.7838 20.37 30.0974 17.5393 29.894 14.9119C29.8008 13.7168 29.4448 13.1235 29.3177 11.7166C29.2244 10.6657 29.4109 10.9962 29.3177 9.70795C29.2244 8.41968 28.9193 6.58051 26.3682 5.93638C24.2833 5.4109 22.7916 6.60594 21.5881 6.58051C20.4524 6.55508 19.1641 5.4109 17.0791 5.93638C14.5196 6.58051 14.2229 8.41968 14.1297 9.70795C14.0365 10.9962 14.2229 10.6741 14.1297 11.7166C14.0026 13.1151 13.6466 13.7168 13.5534 14.9119C13.35 17.5393 13.6635 20.37 14.8671 23.4975Z"
                                                    stroke="#ACB1CA" stroke-miterlimit="10"/>
                                            </svg>

                                        </div>
                                        <div data-value="female"
                                             class="gender-selector p-25 d-flex align-items-center justify-content-center flex-column">
                                            <svg width="41" height="53" viewBox="0 0 41 53" fill="none"
                                                 xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M17.1442 30.1152C17.1442 30.1152 16.7235 32.9757 14.0145 33.6823C13.0975 34.49 11.7767 34.776 14.696 38.5282C17.6153 42.2803 20.3579 42.457 20.3579 42.457C20.3579 42.457 23.3697 42.2719 26.289 38.5282C29.2083 34.7844 27.8875 34.4984 26.9705 33.6823C24.2951 32.9757 23.8745 30.1152 23.8745 30.1152"
                                                    stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                <path
                                                    d="M14.0145 33.6821C14.0145 33.6821 6.34191 34.7674 4.39011 36.9716C2.28688 39.344 -0.952097 44.8882 2.54768 48.2113C6.11476 51.5933 20.3663 51.5933 20.3663 51.5933C20.3663 51.5933 34.887 51.5933 38.4541 48.2113C41.9538 44.8882 38.7065 39.3524 36.6116 36.9716C34.6598 34.7674 26.9872 33.6821 26.9872 33.6821"
                                                    stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                <path
                                                    d="M28.4006 18.7607C28.4006 18.7607 29.5432 16.6822 30.4631 17.7697C31.0985 18.5204 30.6345 20.6676 30.305 21.5344C29.3077 24.16 27.7078 23.9775 27.7078 23.9775"
                                                    stroke="#ACB1CA" stroke-miterlimit="10" stroke-linecap="round"/>
                                                <path
                                                    d="M12.5884 18.7472C12.5884 18.7472 11.4158 16.6855 10.5117 17.7863C9.88737 18.5462 10.3826 20.6864 10.7247 21.5484C11.76 24.1591 13.3571 23.9534 13.3571 23.9534"
                                                    stroke="#ACB1CA" stroke-miterlimit="10" stroke-linecap="round"/>
                                                <path
                                                    d="M12.8198 17.6304C12.8198 17.6304 11.9701 19.8261 13.6864 25.1011C15.2175 29.8207 18.1452 31.192 20.4167 31.2004C22.705 31.2088 25.7 29.8207 27.2312 25.1011C28.9474 19.8261 28.0977 17.6304 28.0977 17.6304"
                                                    stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                <path
                                                    d="M10.6661 17.1508C10.6661 17.1508 12.9797 17.9079 16.5047 16.4693C19.3399 15.3083 19.6848 13.1294 20.4924 13.1294C21.3253 13.1294 21.9395 15.3083 24.396 16.4693C27.8453 18.1014 30.2346 17.1508 30.2346 17.1508"
                                                    stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                <path
                                                    d="M10.6662 17.151C10.6662 17.151 10.5316 11.8088 13.3415 9.26807C16.1514 6.73578 20.4084 6.87038 20.4084 6.87038C20.4084 6.87038 24.7578 6.73578 27.5678 9.26807C30.3777 11.8004 30.2431 17.151 30.2431 17.151"
                                                    stroke="#ACB1CA" stroke-miterlimit="10"/>
                                                <path
                                                    d="M24.7157 3.53039L25.1062 3.21804L24.7157 3.53039ZM30.6048 6.47492L31.0791 6.63303C31.131 6.47734 31.1032 6.30609 31.0047 6.17483C30.9062 6.04356 30.7496 5.96898 30.5856 5.97529L30.6048 6.47492ZM26.8897 8.08323C26.6163 8.04419 26.363 8.23414 26.324 8.50751C26.2849 8.78088 26.4749 9.03414 26.7483 9.07319L26.8897 8.08323ZM15.0945 7.99939C14.6079 6.53952 14.7647 5.3456 15.2747 4.42297C15.7907 3.48946 16.6975 2.78237 17.794 2.36812C20.0123 1.53011 22.8115 1.95045 24.3253 3.84273L25.1062 3.21804C23.2548 0.903862 19.9546 0.48291 17.4406 1.43265C16.171 1.91227 15.0534 2.75631 14.3995 3.93918C13.7396 5.13293 13.5809 6.62062 14.1459 8.31561L15.0945 7.99939ZM24.3253 3.84273C25.6326 5.47685 27.2001 6.26104 28.4386 6.63495C29.0571 6.82167 29.5951 6.90668 29.9813 6.94501C30.1746 6.96419 30.3306 6.97175 30.4405 6.97443C30.4954 6.97577 30.5389 6.97589 30.5698 6.97563C30.5853 6.9755 30.5976 6.97528 30.6067 6.97507C30.6112 6.97496 30.615 6.97485 30.6178 6.97476C30.6193 6.97472 30.6205 6.97468 30.6215 6.97464C30.6221 6.97462 30.6225 6.97461 30.6229 6.97459C30.6231 6.97458 30.6234 6.97457 30.6235 6.97457C30.6237 6.97456 30.624 6.97455 30.6048 6.47492C30.5856 5.97529 30.5858 5.97528 30.586 5.97528C30.586 5.97527 30.5862 5.97527 30.5863 5.97526C30.5865 5.97526 30.5866 5.97525 30.5867 5.97525C30.5869 5.97524 30.5869 5.97524 30.5867 5.97525C30.5863 5.97526 30.585 5.9753 30.583 5.97535C30.579 5.97544 30.5717 5.97558 30.5615 5.97567C30.541 5.97584 30.5085 5.97579 30.4649 5.97473C30.3776 5.9726 30.2466 5.96642 30.0801 5.94989C29.7466 5.9168 29.2738 5.84249 28.7277 5.67762C27.637 5.34835 26.2599 4.66027 25.1062 3.21804L24.3253 3.84273ZM30.6048 6.47492C30.1304 6.31681 30.1305 6.31656 30.1306 6.31633C30.1306 6.31626 30.1307 6.31603 30.1307 6.3159C30.1308 6.31564 30.1309 6.31541 30.131 6.31522C30.1311 6.31483 30.1312 6.3146 30.1312 6.31451C30.1313 6.31433 30.1311 6.31472 30.1308 6.31567C30.1301 6.31758 30.1286 6.3217 30.1263 6.32789C30.1217 6.34027 30.1137 6.36082 30.1022 6.38827C30.079 6.44328 30.0416 6.52526 29.9879 6.62416C29.8799 6.82313 29.7097 7.0835 29.4625 7.33073C28.9826 7.81069 28.189 8.26882 26.8897 8.08323L26.7483 9.07319C28.3935 9.30818 29.4928 8.71467 30.1696 8.03784C30.5008 7.70667 30.725 7.36237 30.8667 7.10125C30.9379 6.97011 30.9893 6.85819 31.0236 6.77682C31.0408 6.73608 31.0537 6.70282 31.0629 6.67844C31.0674 6.66624 31.071 6.65624 31.0737 6.64862C31.0751 6.64481 31.0762 6.6416 31.0771 6.63899C31.0775 6.63769 31.0779 6.63654 31.0783 6.63554C31.0784 6.63505 31.0786 6.63459 31.0787 6.63417C31.0788 6.63396 31.0789 6.63368 31.0789 6.63357C31.079 6.6333 31.0791 6.63303 30.6048 6.47492Z"
                                                    fill="#ACB1CA"/>
                                            </svg>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if(!in_array('relation_ship',$hide ?? []))
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>{{__('web/beneficiary.relation_ship')}}</label>
                                        <select class="form-control required select2" name="relation_ship" data-child="{{ (!in_array('birth_cert',$hide ?? [])) ? '1' : '0' }}">
                                            @foreach(config('static.relation_ships') as $rel)
                                                <option value="{{$rel}}">{{__('mobile.'.$rel)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @if(in_array('relation_ship_parent',$show ?? []))
                                <div id="ask_parent">
                                    <div class="col-12">
                                        <fieldset>
                                            <div class="custom-control custom-switch custom-switch-success mr-2 mb-1">
                                                <p class="mb-0">{{__('mobile.nominee_child_ask')}}</p>
                                                <input name="ask_child_spouse" type="checkbox" value="1" @if((auth()->user()->profile->has_living_spouse_child ?? 0) == '1') checked @endif class="custom-control-input" id="customSwitch11">
                                                <label class="custom-control-label" for="customSwitch11">
                                                    <span class="switch-icon-left"><i class="feather icon-check"></i></span>
                                                    <span class="switch-icon-right"><i class="feather icon-check"></i></span>
                                                </label>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            @endif

                            @endif
                            <div class="col-12" id="birth_cert_div" style="display: none">
                                <div class="form-group">
                                    <label>{{__('web/beneficiary.birth_cert')}}</label>
                                    <input type="file" name="birth_cert" class="form-control">
                                </div>
                            </div>
                            @if(!in_array('nominee_type',$hide ?? []))
                                <div class="col-12 d-none">
                                    <div class="form-group">
                                        <label>{{__('web/beneficiary.nominee_type')}}</label>
                                        <select class="form-control required select2" name="nominee_type">
                                            <option value="trustee" selected>trustee</option>
                                            <option value="hibah">hibah</option>
                                        </select>
                                    </div>
                                </div>
                            @endif
                            @if(!in_array('allocate_percentage',$hide ?? []))
                                <div class="col-12" id="allocate_percentage_div">
                                    <div class="custom-control custom-switch switch-lg custom-switch-success">
                                        <p class="mb-0">{{__('web/beneficiary.allocate_percentage')}} (<span
                                                id="allocate_percentage_value">0</span> %)</p>
                                        <input type="hidden" name="allocate_percentage" value="0">
                                        <div class="my-1" id="allocate_percentage_slider"></div>
                                    </div>
                                </div>
                            @endif

                            @if(in_array('occ',$show ?? []))
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>{{__('web/profile.industry')}}</label>

                                                <select data-value="{{$profile->occupationJob->industry->uuid ?? ''}}"
                                                        class="form-control required select2" name="industry">

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">

                                            <div class="form-group">
                                                <label>{{__('web/profile.job')}}</label>

                                                <select data-value="{{$profile->occupationJob->uuid ?? ''}}"
                                                        class="form-control required select2" name="job">

                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if(in_array('personal_income',$show ?? []))
                                <div class="col-12" id="personal_income_div">
                                    <div class="custom-control custom-switch switch-lg custom-switch-success">
                                        <p class="mb-0">{{__('web/profile.personal_income')}} (RM <span
                                                id="personal_income_value">0</span>)</p>
                                        <input type="hidden" name="personal_income" value="0">
                                        <div class="my-1" id="personal_income_slider"></div>
                                    </div>
                                </div>
                            @endif
                            @if(in_array('household_income',$show ?? []))
                                <div class="col-12" id="household_income_div">
                                    <div class="custom-control custom-switch switch-lg custom-switch-success">
                                        <p class="mb-0">{{__('web/profile.household_income')}} (RM <span
                                                id="household_income_value">0</span>)</p>
                                        <input type="hidden" name="household_income" value="0">
                                        <div class="my-1" id="household_income_slider"></div>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit"
                                class="btn btn-primary addNominee">@lang('web/beneficiary.submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="overlay-bg"></div>

</div>
