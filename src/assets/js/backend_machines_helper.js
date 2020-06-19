/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

(function () {

    'use strict';

    /**
     * MachinesHelper Class
     *
     * This class contains the methods that are used in the backend machines page.
     *
     * @class MachinesHelper
     */
    function MachinesHelper() {
        this.filterResults = {};
    }

    /**
     * Binds the default event handlers of the backend machines page.
     */
    MachinesHelper.prototype.bindEventHandlers = function () {
        var instance = this;

        /**
         * Event: Filter Machines Form "Submit"
         */
        $('#filter-customers form').submit(function (event) {
            var key = $('#filter-customers .key').val();
            $('#filter-customers .selected').removeClass('selected');
            instance.resetForm();
            instance.filter(key);
            return false;
        });

        /**
         * Event: Filter Machines Clear Button "Click"
         */
        $('#filter-customers .clear').click(function () {
            $('#filter-customers .key').val('');
            instance.filter('');
            instance.resetForm();
        });

        /**
         * Event: Filter Entry "Click"
         *
         * Display the machine data of the selected row.
         */
        $(document).on('click', '.entry', function () {
            if ($('#filter-customers .filter').prop('disabled')) {
                return; // Do nothing when user edits a machine record.
            }

            var machineId = $(this).attr('data-id');
            var machine = {};
            $.each(instance.filterResults, function (index, item) {
                if (item.id == machineId) {
                    machine = item;
                    return false;
                }
            });

            instance.display(machine);
            $('#filter-customers .selected').removeClass('selected');
            $(this).addClass('selected');
            $('#edit-customer, #delete-customer').prop('disabled', false);
        });

        /**
         * Event: Appointment Row "Click"
         *
         * Display appointment data of the selected row.
         */
        $(document).on('click', '.appointment-row', function () {
            $('#customer-appointments .selected').removeClass('selected');
            $(this).addClass('selected');

            var machineId = $('#filter-customers .selected').attr('data-id');
            var appointmentId = $(this).attr('data-id');
            var appointment = {};

            $.each(instance.filterResults, function (index, c) {
                if (c.id === machineId) {
                    $.each(c.appointments, function (index, a) {
                        if (a.id == appointmentId) {
                            appointment = a;
                            return false;
                        }
                    });
                    return false;
                }
            });

            instance.displayAppointment(appointment);
        });

        /**
         * Event: Add Machine Button "Click"
         */
        $('#add-customer').click(function () {
        
            instance.resetForm();
            $('#add-edit-delete-group').hide();
            $('#save-cancel-group').show();
            $('.record-details').find('input, textarea').prop('readonly', false);

            $('#filter-customers button').prop('disabled', true);
            $('#filter-customers .results').css('color', '#AAA');
        });

        /**
         * Event: Edit Machine Button "Click"
         */
        $('#edit-customer').click(function () {
            $('.record-details').find('input, textarea').prop('readonly', false);
            $('#add-edit-delete-group').hide();
            $('#save-cancel-group').show();

            $('#filter-customers button').prop('disabled', true);
            $('#filter-customers .results').css('color', '#AAA');
        });

        /**
         * Event: Cancel Machine Add/Edit Operation Button "Click"
         */
        $('#cancel-customer').click(function () {
            var id = $('#customer-id').val();
            instance.resetForm();
            if (id != '') {
                instance.select(id, true);
            }
        });

        /**
         * Event: Save Add/Edit Machine Operation "Click"
         */
        $('#save-customer').click(function () {
            var machine = {
                machine_type: $('#machine-type').val(),
                machine_id: $('#machine-physical-id').val(),
                email: $('#email').val(),
                current_location: $('#current-location').val(),
                machine_make: $('#machine-make').val(),
                manufacture_year: $('#manufacture-year').val(),
                
                notes: $('#notes').val()
            };
            // console.log(machine);
            if ($('#customer-id').val() != '') {
                machine.id = $('#customer-id').val();
            }

            if (!instance.validate()) {
                return;
            }

            instance.save(machine);
        });

        /**
         * Event: Delete Machine Button "Click"
         */
        $('#delete-customer').click(function () {
          
            var machineId = $('#customer-id').val();
            var buttons = [
                {
                    text: EALang.delete,
                    click: function () {
                        instance.delete(machineId);
                        $('#message_box').dialog('close');
                    }
                },
                {
                    text: EALang.cancel,
                    click: function () {
                        $('#message_box').dialog('close');
                    }
                }
            ];

            GeneralFunctions.displayMessageBox(EALang.delete_customer,
                EALang.delete_record_prompt, buttons);
        });
    };

    /**
     * Save a machine record to the database (via ajax post).
     *
     * @param {Object} machine Contains the machine data.
     */
    MachinesHelper.prototype.save = function (machine) {
        var postUrl = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_save_machine';
        var postData = {
            csrfToken: GlobalVariables.csrfToken,
            machine: JSON.stringify(machine)
        };

        $.post(postUrl, postData, function (response) {
            if (!GeneralFunctions.handleAjaxExceptions(response)) {
                return;
            }

            Backend.displayNotification(EALang.customer_saved);
            this.resetForm();
            $('#filter-customers .key').val('');
            this.filter('', response.id, true);
        }.bind(this), 'json').fail(GeneralFunctions.ajaxFailureHandler);
    };

    /**
     * Delete a machine record from database.
     *
     * @param {Number} id Record id to be deleted.
     */
    MachinesHelper.prototype.delete = function (id) {
        
        var postUrl = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_delete_machine';
        var postData = {
            csrfToken: GlobalVariables.csrfToken,
            machine_id: id
        };
        
        $.post(postUrl, postData, function (response) {
            if (!GeneralFunctions.handleAjaxExceptions(response)) {
                return;
            }

            Backend.displayNotification(EALang.customer_deleted);
            this.resetForm();
            this.filter($('#filter-customers .key').val());
        }.bind(this), 'json').fail(GeneralFunctions.ajaxFailureHandler);
    };

    /**
     * Validate machine data before save (insert or update).
     */
    MachinesHelper.prototype.validate = function () {
        $('#form-message')
            .removeClass('alert-danger')
            .hide();
        $('.has-error').removeClass('has-error');

        try {
            // Validate required fields.
            var missingRequired = false;

            $('.required').each(function () {
                if ($(this).val() == '') {
                    $(this).closest('.form-group').addClass('has-error');
                    missingRequired = true;
                }
            });

            if (missingRequired) {
                throw EALang.fields_are_required;
            }

            // Validate email address.
            if (!GeneralFunctions.validateEmail($('#email').val())) {
                $('#email').closest('.form-group').addClass('has-error');
                throw EALang.invalid_email;
            }

            return true;
        } catch (message) {
            $('#form-message')
                .addClass('alert-danger')
                .text(message)
                .show();
            return false;
        }
    };

    /**
     * Bring the machine form back to its initial state.
     */
    MachinesHelper.prototype.resetForm = function () {
        $('.record-details').find('input, textarea').val('');
        $('.record-details').find('input, textarea').prop('readonly', true);

        $('#customer-appointments').empty();
        $('#appointment-details').toggleClass('hidden', true).empty();
        $('#edit-customer, #delete-customer').prop('disabled', true);
        $('#add-edit-delete-group').show();
        $('#save-cancel-group').hide();

        $('.record-details .has-error').removeClass('has-error');
        $('.record-details #form-message').hide();

        $('#filter-customers button').prop('disabled', false);
        $('#filter-customers .selected').removeClass('selected');
        $('#filter-customers .results').css('color', '');
    };

    /**
     * Display a machine record into the form.
     *
     * @param {Object} machine Contains the machine record data.
     */
    MachinesHelper.prototype.display = function (machine) {
        $('#customer-id').val(machine.id);
        $('#machine-type').val(machine.machine_type);
        $('#machine-physical-id').val(machine.machine_id);
        $('#email').val(machine.email);
        $('#current-location').val(machine.current_location);
        $('#machine-make').val(machine.machine_make);
        $('#city').val(machine.city);
        $('#manufacture-year').val(machine.manufacture_year);
        $('#notes').val(machine.notes);
        
        $('#customer-appointments').empty();
        $.each(machine.appointments, function (index, appointment) {
            if (GlobalVariables.user.role_slug === Backend.DB_SLUG_PROVIDER && parseInt(appointment.id_users_provider) !== GlobalVariables.user.id) {
                return true; // continue
            }

            if (GlobalVariables.user.role_slug === Backend.DB_SLUG_SECRETARY && GlobalVariables.secretaryProviders.indexOf(appointment.id_users_provider) === -1) {
                return true; // continue
            }

            var start = GeneralFunctions.formatDate(Date.parse(appointment.start_datetime), GlobalVariables.dateFormat, true);
            var end = GeneralFunctions.formatDate(Date.parse(appointment.end_datetime), GlobalVariables.dateFormat, true);
            var html =
                '<div class="appointment-row" data-id="' + appointment.id + '">' +
                start + ' - ' + end + '<br>' +
                appointment.service.name + ', ' +
                appointment.provider.first_name + ' ' + appointment.provider.last_name +
                '</div>';
            $('#customer-appointments').append(html);
        });

        $('#appointment-details').empty();
    };

    /**
     * Filter machine records.
     *
     * @param {String} key This key string is used to filter the machine records.
     * @param {Number} selectId Optional, if set then after the filter operation the record with the given
     * ID will be selected (but not displayed).
     * @param {Boolean} display Optional (false), if true then the selected record will be displayed on the form.
     */
    MachinesHelper.prototype.filter = function (key, selectId, display) {
        display = display || false;
        // console.log(key + '\n bob' + selectId + '\n' +  display)
        var postUrl = GlobalVariables.baseUrl + '/index.php/backend_api/ajax_filter_machines';
        var postData = {
            csrfToken: GlobalVariables.csrfToken,
            key: key
        };
        
        $.post(postUrl, postData, function (response) {
            if (!GeneralFunctions.handleAjaxExceptions(response)) {
                return;
            }

            this.filterResults = response;

            $('#filter-customers .results').html('');
            $.each(response, function (index, machine) {
                var html = this.getFilterHtml(machine);
                $('#filter-customers .results').append(html);
            }.bind(this));
            if (response.length == 0) {
                $('#filter-customers .results').html('<em>' + EALang.no_records_found + '</em>');
            }

            if (selectId != undefined) {
                this.select(selectId, display);
            }

        }.bind(this), 'json').fail(GeneralFunctions.ajaxFailureHandler);
    };

    /**
     * Get the filter results row HTML code.
     *
     * @param {Object} machine Contains the machine data.
     *
     * @return {String} Returns the record HTML code.
     */
    MachinesHelper.prototype.getFilterHtml = function (machine) {
   
        // var name = customer.first_name + ' ' + customer.last_name;
        // var info = customer.email;
        // info = (customer.phone_number != '' && customer.phone_number != null)
            // ? info + ', ' + customer.phone_number : info;

        var html =
            '<div class="entry" data-id="' + machine.id + '">' +
            '<strong>' +
            machine.machine_make + ' ' + machine.machine_id + 
            '</strong><br>' +
            'info' +
            '</div><hr>';

        return html;
    };

    /**
     * Select a specific record from the current filter results.
     *
     * If the machine id does not exist in the list then no record will be selected.
     *
     * @param {Number} id The record id to be selected from the filter results.
     * @param {Boolean} display Optional (false), if true then the method will display the record
     * on the form.
     */
    MachinesHelper.prototype.select = function (id, display) {
        display = display || false;

        $('#filter-customers .selected').removeClass('selected');

        $('#filter-customers .entry').each(function () {
            if ($(this).attr('data-id') == id) {
                $(this).addClass('selected');
                return false;
            }
        });
        
        if (display) {
            $.each(this.filterResults, function (index, machine) {
                if (machine.id == id) {
                    this.display(machine);
                    $('#edit-customer, #delete-customer').prop('disabled', false);
                    return false;
                }
            }.bind(this));
        }
    };

    /**
     * Display appointment details on machines backend page.
     *
     * @param {Object} appointment Appointment data
     */
    MachinesHelper.prototype.displayAppointment = function (appointment) {
        var start = GeneralFunctions.formatDate(Date.parse(appointment.start_datetime), GlobalVariables.dateFormat, true);
        var end = GeneralFunctions.formatDate(Date.parse(appointment.end_datetime), GlobalVariables.dateFormat, true);

        var html =
            '<div>' +
            '<strong>' + appointment.service.name + '</strong><br>' +
            appointment.provider.first_name + ' ' + appointment.provider.last_name + '<br>' +
            start + ' - ' + end + '<br>' +
            '</div>';

        $('#appointment-details').html(html).removeClass('hidden');
    };

    window.MachinesHelper = MachinesHelper;
})();
