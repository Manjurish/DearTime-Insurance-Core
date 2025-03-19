@extends('vendor.ariel.layout')
@section('title','Reports')
@section('contenter')
    <section>
        <div class="card-header">
            <h4 class="card-title">Underwriting Rejection Analysis</h4>
        </div>
        <!-- Begin Users Profile -->
        <div class="card">
            <div class="card-body">
                <div class="card-dashboard">
                    <div class="table-responsive">

                        <table id="table" class="display table table-data-width">
                            <thead>
                            <tr>
                                <th>i</th>
                                {{--<th>RefNo</th>--}}
                                <th>User</th>
                                <th>Mykad/Passport</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Answers</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Medical Answers</th>
                            </tr>
                            </thead>
                            <tbody>
                                @php $i = 1 @endphp

                                @foreach($data as $item)
                                    @continue($item->answers == null)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    {{--<td>{{ $item->ref_no }}</td>--}}
                                    <td>
                                        <a href="{{ route('admin.User.details', $item->individual->uuid) }}">
                                            <img src="{{ $item->individual->selfie ?? '' }}" style="width:50px;height:50px" />
                                            {{ $item->individual->name }}
                                        </a>
                                    </td>
                                    <td>{{ $item->individual->nric }}</td>
                                    <td>{{ $item->individual->age() }}</td>
                                    <td>{{ $item->individual->gender }}</td>
                                    <td>
                                        Weight: {{ $item->answers['weight'] }} <br>
                                        Height: {{ $item->answers['height'] }} <br>
                                        Smoke: {{ $item->answers['smoke'] }} <br>
                                    </td>
                                    <td>
                                        {{ $item->death == 1 ? App\Helpers\Enum::UNDERWRITING_ACCEPTED : App\Helpers\Enum::UNDERWRITING_REJECTED }}
                                    </td>
                                    <td>
                                        {{ $item->creator->name }}
                                    </td>
                                    <td>
                                        {{ $item->created_at }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.reports.underwriting.rejection.analysis.by.user', $item->uuid) }}">
                                            <i class="feather icon-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
@section('mystyle')
    <link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/datatables.min.css')}}">
    <link rel="stylesheet" href="{{ asset('css/pages/data-list-view.css') }}">
@endsection
@section('myscript')
    <script src="{{asset('vendors/js/tables/datatable/datatables.min.js')}}"></script>
    <script src="{{asset('vendors/js/tables/datatable/datatables.bootstrap4.min.js')}}"></script>
    <script src="{{asset('js/scripts/data-list-view.js')}}"></script>
    <script>
        $(document).ready(function() {
            $('#table').DataTable( {
                select: {
                    style:    'os',
                    selector: 'td:first-child'
                },
                // order: [[ 1, 'asc' ]],
                "ordering": false,
                "lengthMenu": [[20, 50, -1], [20, 50, "All"]],
                language: {
                    search: "_INPUT_",
                    "search": '<i class="fa fa-search"></i>',
                    "searchPlaceholder": "search",
                }
            } );
        });
    </script>
@endsection
