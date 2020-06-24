<?php defined('BASEPATH') OR exit('No direct script access allowed');

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

/**
 * Machines Model
 *
 * @package Models
 */
class Machine_types_model extends CI_Model {
    /**
     * Get all machine types
     *
     * @example $this->Model->getBatch('id = ' . $recordId);
     *
     * @param string $whereClause (OPTIONAL) The WHERE clause of the query to be executed. DO NOT INCLUDE 'WHERE'
     * KEYWORD.
     *
     * @return array Returns the rows from the database.
     */
    public function get_batch($where_clause = '')
    {
        

        // $machines_role_id = $this->get_machines_role_id();

        if ($where_clause != '')
        {
            $this->db->where($where_clause);
        }

        // $this->db->where('id_roles', $machines_role_id);

        return $this->db->get('ea_machine_types')->result_array();
    }
}