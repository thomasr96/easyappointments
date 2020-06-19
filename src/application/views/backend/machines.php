<script src="<?= asset_url('assets/ext/jquery-ui/jquery-ui-timepicker-addon.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_machines_helper.js') ?>"></script>
<script src="<?= asset_url('assets/js/backend_machines.js') ?>"></script>
<script>
    var GlobalVariables = {
        csrfToken          : <?= json_encode($this->security->get_csrf_hash()) ?>,
        availableProviders : <?= json_encode($available_providers) ?>,
        availableServices  : <?= json_encode($available_services) ?>,
        secretaryProviders : <?= json_encode($secretary_providers) ?>,
        dateFormat         : <?= json_encode($date_format) ?>,
        timeFormat         : <?= json_encode($time_format) ?>,
        baseUrl            : <?= json_encode($base_url) ?>,
        machines          : <?= json_encode($machines) ?>,
        // user is the current signed in user
        user               : {
            id         : <?= $user_id ?>,
            email      : <?= json_encode($user_email) ?>,
            role_slug  : <?= json_encode($role_slug) ?>,
            privileges : <?= json_encode($privileges) ?>
        }
    };

    $(document).ready(function() {
        BackendMachines.initialize(true);
    });
</script>

<div id="customers-page" class="container-fluid backend-page">
    <div class="row">
    	<div id="filter-customers" class="filter-records column col-xs-12 col-sm-5">
    		<form>
                <div class="input-group">
                    <input type="text" class="key form-control">

                    <div class="input-group-addon">
                        <div>
                            <button class="filter btn btn-default" type="submit" title="<?= lang('filter') ?>">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                            <button class="clear btn btn-default" type="button" title="<?= lang('clear') ?>">
                                <span class="glyphicon glyphicon-repeat"></span>
                            </button>
                        </div>
                    </div>
                </div>
    		</form>

            <h3><?= lang('customers') ?></h3>
            <div class="results"></div>
    	</div>

    	<div class="record-details col-xs-12 col-sm-7">
            <div class="btn-toolbar">
                <div id="add-edit-delete-group" class="btn-group">
                    <?php if ($privileges[PRIV_CUSTOMERS]['add'] === TRUE): ?>
                    <button id="add-customer" class="btn btn-primary">
                        <span class="glyphicon glyphicon-plus"></span>
                        <?= lang('add') ?>
                    </button>
                    <?php endif ?>

                    <?php if ($privileges[PRIV_CUSTOMERS]['edit'] === TRUE): ?>
                    <button id="edit-customer" class="btn btn-default" disabled="disabled">
                        <span class="glyphicon glyphicon-pencil"></span>
                        <?= lang('edit') ?>
                    </button>
                    <?php endif ?>

                    <?php if ($privileges[PRIV_CUSTOMERS]['delete'] === TRUE): ?>
                    <button id="delete-customer" class="btn btn-default" disabled="disabled">
                        <span class="glyphicon glyphicon-remove"></span>
                        <?= lang('delete') ?>
                    </button>
                    <?php endif ?>
                </div>

                <div id="save-cancel-group" class="btn-group" style="display:none;">
                    <button id="save-customer" class="btn btn-primary">
                        <span class="glyphicon glyphicon-ok"></span>
                        <?= lang('save') ?>
                    </button>
                    <button id="cancel-customer" class="btn btn-default">
                        <i class="glyphicon glyphicon-ban-circle"></i>
                        <?= lang('cancel') ?>
                    </button>
                </div>
            </div>

            <input id="customer-id" type="hidden">

            <div class="row">
                <div class="col-xs-12 col-sm-6" style="margin-left: 0;">
                    <h3><?= lang('details') ?></h3>

                    <div id="form-message" class="alert" style="display:none;"></div>

                    <div class="form-group">
                        <label class="control-label" for="machine-type">Type</label>
                        <input id="machine-type" class="form-control required">
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="machine-physical-id">ID</label>
                        <input id="machine-physical-id" class="form-control required">
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="email">Email</label>
                        <input id="email" class="form-control required">
                    </div>


                    <div class="form-group">
                        <label class="control-label" for="current-location">Current Location</label>
                        <input id="current-location" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="machine-make">Make</label>
                        <input id="machine-make" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="manufacture-year">Manufacture Year</label>
                        <input id="manufacture-year" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="notes"><?= lang('notes') ?></label>
                        <textarea id="notes" rows="4" class="form-control"></textarea>
                    </div>

                    <p class="text-center">
                        <em id="form-message" class="text-danger"><?= lang('fields_are_required') ?></em>
                    </p>
                </div>

                <div class="col-xs-12 col-sm-6">
                    <h3><?= lang('appointments') ?></h3>
                    <div id="customer-appointments" class="well"></div>
                    <div id="appointment-details" class="well hidden"></div>
                </div>
            </div>
    	</div>
    </div>
</div>
