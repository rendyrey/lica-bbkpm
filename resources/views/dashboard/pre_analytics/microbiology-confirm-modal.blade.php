<!-- Horizontal form modal -->
<div id="micro-confirm-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Microbiology Options</h5>
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    X
                </div>
                <!--end::Close-->
            </div>
            <div class="modal-body">

                <div class="edit-patient-details-form">
                    {!! Form::open(['class'=>'form form-horizontal form-validate-jquery', 'id' => 'form-update-micro-confirm', 'method' => 'put']) !!}
                    {{ Form::hidden('id') }}
                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">Serial Number</label></div>
                        <div class="col-md-9">
                            {{ Form::text('serial_number', null, ['class' => 'form-control form-control-solid form-control-sm', 'id' => 'serial_number']) }}
                        </div>
                    </div>
                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">Register Number</label></div>
                        <div class="col-md-9">
                            {{ Form::text('register_number', null, ['class' => 'form-control form-control-solid form-control-sm', 'id' => 'register_number']) }}
                        </div>
                    </div>
                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">Identity Number</label></div>
                        <div class="col-md-9">
                            {{ Form::text('identity_number', null, ['class' => 'form-control form-control-solid form-control-sm', 'id' => 'identity_number']) }}
                        </div>
                    </div>

                    <!-- End Input -->
                </div>
                <div class="mb-2 mt-8">
                    {{ Form::submit('Submit', ['class' => 'form-control btn btn-light-success']) }}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
<!-- /horizontal form modal -->