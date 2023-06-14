@extends('dashboard.main_layout')

@section('styles')
<link href="{{asset('metronic_assets/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<!--begin::Content-->
<div class="docs-content d-flex flex-column flex-column-fluid" id="kt_docs_content">
    <!--begin::Container-->
    <div class="px-5 mx-5" id="kt_docs_content_container">
        <div class="row">
            <div class="col-lg-12">
                <!--begin::Card-->
                <div class="card card-docs mb-2">
                    <!--begin::Card Body-->
                    <div class="card-body fs-6 py-15 px-5 py-lg-8 px-lg-8 text-gray-700">
                        <!--begin::Section-->
                        <div class="p-0">
                            <!--begin::Heading-->
                            <h1 class="anchor fw-bolder mb-5">
                                Master {{ ucwords($masterData) }}</h1>
                            <!--begin::Alert-->
                            <div class="alert alert-{{session('panel') ? session('panel') : ''}} d-flex align-items-center p-5 {{session('panel') ? '' : 'hidden'}}">
                                <!--begin::Icon-->
                                <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                                <!--end::Icon-->

                                <!--begin::Wrapper-->
                                <div class="d-flex flex-column">
                                    <!--begin::Content-->
                                    <span>{{session('message')}}</span>
                                    <!--end::Content-->
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Alert-->
                            <!--end::Heading-->
                            <!--begin::CRUD-->
                            <div class="row">
                                <div class="col">
                                    <a href="{{ route('export-patient') }}"><button class="btn btn-primary btn-sm">Export Data</button></a>
                                </div>
                               
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <span>Import Patient Data</span>
                                    {!! Form::open(['route' => 'import-patient', 'method' => 'post', 'class'=>'form form-horizontal', 'files' => true]) !!}
                                        {{ Form::file('file_excel', ['class' => 'form-control form-control-sm mb-2', 'required']) }}
                                        {{ Form::submit('Import', ['class' => 'btn btn-success btn-sm']) }}
                                    {!! Form::close() !!}
                                </div>
                            </div>
                            <div class="py-5">
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-stack flex-wrap mb-5">
                                    <!--begin::Search-->
                                    <div class="d-flex align-items-center position-relative my-1 mb-2 mb-md-0">
                                        <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                                        <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="black" />
                                                <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="black" />
                                            </svg>
                                        </span>
                                        <!--end::Svg Icon-->
                                        <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-sm form-control-solid w-250px ps-15" placeholder="Search {{ ucwords($masterData) }}" />
                                    </div>
                                    <!--end::Search-->
                                    <!--begin::Toolbar-->
                                    {{-- <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                                        <!--begin::Add customer-->
                                        <button type="button" class="btn btn-sm btn btn-light-primary btn-hover-rise" data-bs-toggle="tooltip" title="Add new {{ $masterData }}">
                                    Add {{ ucwords($masterData) }}</button>
                                    <!--end::Add customer-->
                                </div> --}}
                                <!--end::Toolbar-->
                                <!--begin::Group actions-->
                                <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">
                                    <div class="fw-bolder me-5">
                                        <span class="me-2" data-kt-docs-table-select="selected_count"></span>Selected
                                    </div>
                                    <button type="button" class="btn btn-danger" data-kt-docs-table-select="delete_selected">Selection Action</button>
                                </div>
                                <!--end::Group actions-->
                            </div>
                            <!--end::Wrapper-->
                            <!--begin::Datatable-->
                            <table class="table gy-1 align-middle table-striped px-0 datatable-ajax">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th>NIK</th>
                                        <th>Name</th>
                                        <th>Medrec</th>
                                        <th>Gender</th>
                                        <th>Birthdate</th>
                                        <th>Phone</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th class="text-end min-w-100px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-bold"></tbody>
                            </table>
                            <!--end::Datatable-->
                        </div>
                        <!--end::CRUD-->
                    </div>
                    <!--end::Section-->
                </div>
                <!--end::Card Body-->
            </div>
            <!--end::Card-->
        </div>
    </div>

</div>
<!--end::Container-->


</div>
<!--end::Content-->



<!-- Horizontal form modal -->
<div id="modal_form_horizontal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update data {{ $masterData }}</h5>
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    X
                </div>
                <!--end::Close-->
            </div>
            <div class="modal-body">
                {!! Form::open(['class'=>'form form-horizontal form-validate-jquery', 'id' => 'form-edit', 'method' => 'put']) !!}
                {{ Form::hidden('id') }}
                <div class="mb-4">
                    <label class="form-label fs-6">NIK</label>
                    {{ Form::text('nik', null, ['class' => 'form-control form-control-solid form-control-sm']) }}

                </div>
                <div class="mb-4">
                    <label class="form-label fs-6">Patient Name</label>
                    {{ Form::text('name', null, ['class' => 'form-control form-control-solid form-control-sm', 'id' => 'first-input']) }}

                </div>
                <div class="mb-4">
                    <label for="basic-url" class="form-label">Email</label>
                    {{ Form::text('email', null, ['class' => 'form-control form-control-solid form-control-sm']) }}
                </div>
                <div class="mb-4">
                    <label for="basic-url" class="form-label">Phone Number</label>
                    {{ Form::text('phone', null, ['class' => 'form-control form-control-solid form-control-sm']) }}
                </div>
                <div class="mb-4">
                    <label for="basic-url" class="form-label">Medical Record</label>
                    {{ Form::text('medrec', null, ['class' => 'form-control form-control-solid form-control-sm']) }}
                </div>
                <div class="mb-4">
                    <label for="basic-url" class="form-label">Birthdate</label>
                    {{ Form::text('birthdate', null, ['class' => 'form-control form-control-solid form-control-sm birthdate']) }}
                </div>
                <div class="mb-4">
                    <label for="basic-url" class="form-label">Gender</label>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-check form-check-custom form-check-solid me-10">
                                {{ Form::radio('gender', 'M', null, ['class' => 'form-check-input h-15px w-15px', 'id' => 'radio-male']) }}
                                <label class="form-check-label mr-1" for="radio-male">
                                    Laki-laki
                                </label>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-check form-check-custom form-check-solid me-10">
                                {{ Form::radio('gender', 'M', null, ['class' => 'form-check-input h-15px w-15px', 'id' => 'radio-female']) }}
                                <label class="form-check-label" for="radio-female">
                                    Perempuan
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="basic-url" class="form-label">Address</label>
                    {{ Form::textarea('address', null, ['class' => 'h-80px form-control form-control-solid form-control-sm']) }}
                </div>
                <div class="mb-2 mt-8">
                    {{ Form::submit('Update ' . $masterData, ['class' => 'form-control btn btn-light-success']) }}
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
<!-- /horizontal form modal -->
@endsection

@section('scripts')

<!-- Form validation -->
<script src="{{asset('limitless_assets/js/plugins/forms/validation/validate.min.js')}}"></script>
<!-- /Form validation -->

<script src="{{asset('metronic_assets/plugins/custom/datatables/datatables.bundle.js')}}"></script>
<script src="{{asset('js/master/master-'.$masterData.'-page.js')}}"></script>
<script src="{{asset('js/master/global.js')}}"></script>
@endsection