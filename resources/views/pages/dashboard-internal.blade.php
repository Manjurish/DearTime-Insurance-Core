@extends('layouts.contentLayoutMaster')
@section('title', 'Dashboard')
@section('content')

{{-- <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script> --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="">
                        <canvas id="canvas1" height="280" width="600"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="">
                        <canvas id="canvas2" height="280" width="600"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <a href="{{ route('admin.Payment.index') }}" class="hover:bg-gray-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">RM{{ number_format($totalPremiumReceived,2) }}</h3>
                                <p class="mb-0">Total Premium Received</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-money fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <a href="{{ route('admin.User.index') }}" class="hover:bg-gray-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">{{ number_format($individualCount) }}</h3>
                                <p class="mb-0">Individual Users</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-user fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <a href="{{ route('admin.User.index') }}" class="hover:bg-gray-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">{{ number_format($corporateCount) }}</h3>
                                <p class="mb-0">Corporate Users</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-users fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card">
                <a href="{{ route('admin.claim.index') }}" class="hover:bg-gray-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">{{ number_format($claimsCount) }}</h3>
                                <p class="mb-0">Claims</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fa fa-clipboard fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
            {{-- <a href="{{ route('admin.claim.index') }}" class="hover:bg-gray-100"> --}}
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">RM {{ number_format($charityfundsum,2) }}</h3>
                                <p class="mb-0">Charity Fund</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hand-holding-heart fa-3x"></i>            
                            
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
            {{-- <a href="{{ route('admin.claim.index') }}" class="hover:bg-gray-100"> --}}
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">RM {{ number_format($soponhold_fund,2) }}</h3>
                                <p class="mb-0">Charity Fund On Hold</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hand-holding-heart fa-3x"></i>            
                            
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
            {{-- <a href="{{ route('admin.claim.index') }}" class="hover:bg-gray-100"> --}}
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">{{ number_format($sopcovered) }}</h3>
                                <p class="mb-0">People got Covered So far</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hand-holding-heart fa-3x"></i>            
                            
                            </div>
                        </div>
                        
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
            {{-- <a href="{{ route('admin.claim.index') }}" class="hover:bg-gray-100"> --}}
                    <div class="card-body">
                        <div class="d-flex justify-content-between px-md-1">
                            <div>
                                <h3 class="text-danger">{{ number_format($sopinline) }}</h3>
                                <p class="mb-0">People waiting in-line</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-hand-holding-heart fa-3x"></i>            
                            
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

    </div>
   

</div>


</div>
@endsection

@section('myscript')
    <script src="{{ asset('/js/bootstrap-session-timeout.min.js') }}"></script>
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>

    <script>
        $.sessionTimeout({
            title: 'Session Alert',
            message : "You're session is about to expire! Would you like to stay on this page or logout?",
            keepAliveUrl: '{{ route('admin.refresh.csrf') }}',
            logoutUrl: '{{ route('logout') }}',
            redirUrl: '{{ route('logout') }}',
            warnAfter: 600000,
            redirAfter: 60000,
            countdownMessage: 'Redirecting in {timer} seconds.'
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type:'POST',
            url:'{{ route('admin.refresh.csrf') }}',
            success:function(data){
                $('meta[name="csrf-token"]').attr('content', data);
            }
        });

    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>

    <script>
        var labels =<?php      echo $labels; ?>;

        var users = <?php      echo $users; ?>;

        var usersData = {
            labels: labels,
            datasets: [{
                label: 'User',
                backgroundColor: "pink",
                data: users
            }]
        };


        var orders = <?php      echo $orders; ?>;

        var ordersData = {
            labels: labels,
            datasets: [{
                label: 'Order',
                backgroundColor: "pink",
                data: orders
            }]
        };

        window.onload = function() {

            var canvas1 = document.getElementById("canvas1").getContext("2d");
            window.myBar = new Chart(canvas1, {
                type: 'line',
                data: usersData,
                options: {
                    elements: {
                        rectangle: {
                            borderWidth: 2,
                            borderColor: '#c1c1c1',
                            borderSkipped: 'bottom'
                        }
                    },
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Monthly Customer Registered'
                    }
                }
            });

            var canvas2 = document.getElementById("canvas2").getContext("2d");
            window.myBar = new Chart(canvas2, {
                type: 'pie',
                data: ordersData,
                options: {
                    elements: {
                        rectangle: {
                            borderWidth: 2,
                            borderColor: '#c1c1c1',
                            borderSkipped: 'bottom'
                        }
                    },
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Monthly Order'
                    }
                }
            });
        };

    </script>
@endsection