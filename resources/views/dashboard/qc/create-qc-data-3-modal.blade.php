<!-- Horizontal form modal -->
<div id="create-qc-data-3-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add QC Data (Level 3)</h5>
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    X
                </div>
                <!--end::Close-->
            </div>
            <div class="modal-body">
                {!! Form::open(['class'=>'form form-horizontal form-validate-jquery', 'id' => 'form-create-level-3']) !!}
                <div class="edit-patient-details-form">
                    <input type="hidden" id="qc_id3_3" name="qc_id3_3" class="form-control form-control-solid form-control-sm">
                    <input type="hidden" id="qc_id1_3" name="qc_id1_3" class="form-control form-control-solid form-control-sm">
                    <input type="hidden" id="qc_id2_3" name="qc_id2_3" class="form-control form-control-solid form-control-sm">

                    <div class="fv-row row">
                        <div class="col-md-3"><label class="form-label fs-7">Lot No.</label></div>
                        <div class="col-md-9">
                            <p id="lot_no_lv_3"> </p>
                        </div>
                    </div>
                    <div class="fv-row row">
                        <div class="col-md-3"><label class="form-label fs-7">Month</label></div>
                        <div class="col-md-9">
                            <p id="month_year_lv_3"> </p>
                        </div>
                    </div>
                    <div class="fv-row row">
                        <div class="col-md-3"><label class="form-label fs-7">Test Name</label></div>
                        <div class="col-md-9">
                            <p id="test_name_lv_3"> </p>
                        </div>
                    </div>
                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">Date</label></div>
                        <div class="col-md-9">
                            <!-- <input class="form-control form-control-solid form-control-sm" placeholder="Pick date range" id="daterange-picker" /> -->
                            <input class="form-control form-control-solid form-control-sm daterange-picker" placeholder="Pick date range" id="date_3" name="date_3" />
                        </div>
                    </div>
                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">QC Data</label></div>
                        <div class="col-md-9">
                            {{ Form::text('qc_data_3', null, ['class' => 'form-control form-control-solid form-control-sm', 'id' => 'qc_data_3', 'onfocusout' => 'QCDataonFocusOut3()']) }}
                            <!-- <input type="text" id="qc_data" class="form-control form-control-solid form-control-sm"> -->
                        </div>
                    </div>
                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">Position (SD)</label></div>
                        <div class="col-md-9">
                            {{ Form::text('position_3', null, ['class' => 'form-control form-control-solid form-control-sm', 'id' => 'position_3', 'readonly']) }}
                            <!--  -->
                            <!-- <input type="text" id="position" class="form-control form-control-solid form-control-sm"> -->
                        </div>
                    </div>
                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">QC</label></div>
                        <div class="col-md-9">
                            {{ Form::number('qc_3', null, ['class' => 'form-control form-control-solid form-control-sm']) }}
                            <!-- <input type="number" id="qc" class="form-control form-control-solid form-control-sm"> -->
                        </div>
                    </div>
                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">Medical Laboratory Technologist</label></div>
                        <div class="col-md-9">
                            <!-- {{ Form::text('atlm', null, ['class' => 'form-control form-control-solid form-control-sm']) }} -->
                            <input type="text" id="atlm_3" name="atlm_3" value="{{ Auth::user()->name }}" class="form-control form-control-solid form-control-sm" readonly>
                        </div>
                    </div>
                    <div class="fv-row row mb-4">
                        <div class="col-md-3"><label class="form-label fs-7">Recommendation</label></div>
                        <div class="col-md-9">
                            {{ Form::text('recommendation_3', null, ['class' => 'form-control form-control-solid form-control-sm']) }}
                            <!-- <input type="text" id="recommendation" class="form-control form-control-solid form-control-sm"> -->
                        </div>
                    </div>

                    <!-- End Input -->
                </div>
                <div class="mb-2 mt-8">
                    {{ Form::submit('Add QC Data', ['class' => 'form-control btn btn-light-success']) }}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
<!-- /horizontal form modal -->