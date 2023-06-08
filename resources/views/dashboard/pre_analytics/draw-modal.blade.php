<!-- Horizontal form modal -->
<div id="draw-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Draw Options</h5>
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    X
                </div>
                <!--end::Close-->
            </div>
            <div class="modal-body">

                <div class="edit-patient-details-form">
                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">Sampling By</label></div>
                        <div class="col-md-9">
                            <!-- {{ Form::select('user_id', [], null, ['class' => 'form-select form-select-sm form-select-solid select-two select-user','required'=>true, 'data-control' => 'select2', 'data-placeholder' => 'Select Users','id'=>'user_id']) }} -->
                            <select id="user_list" class="form-select form-select-sm form-select-solid select-two select-user" required="true" data-control="select2" data-placeholder="Select Users">

                            </select>
                        </div>
                    </div>

                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">Sample Quality</label></div>
                        <div class="col-md-9">
                            <input type="text" class="form-control form-control-solid form-control-sm" id="sampling_reason">
                        </div>
                    </div>

                    <!-- End Input -->
                </div>
                <div class="mb-2 mt-8">
                    <button type="button" class="form-control btn btn-light-primary btnFinalDrawAll" type="button" id="btn-final-draw-all" data-transaction-id="">Draw All</button>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- /horizontal form modal -->