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
class Machines_Model extends CI_Model {
    /**
     * Add a machine record to the database.
     *
     * This method adds a machine to the database. If the machine doesn't exists it is going to be inserted, otherwise
     * the record is going to be updated.
     *
     * @param array $machine Associative array with the machine's data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the machine id.
     */
    public function add($machine)
    {
        // Validate the machine data before doing anything.
        $this->validate($machine);

        // :: CHECK IF machine ALREADY EXIST (FROM EMAIL).
        if ($this->exists($machine) && ! isset($machine['id']))
        {
            // Find the machine id from the database.
            $machine['id'] = $this->find_record_id($machine);
        }

        // :: INSERT OR UPDATE machine RECORD
        if ( ! isset($machine['id']))
        {
            $machine['id'] = $this->_insert($machine);
        }
        else
        {
            $this->_update($machine);
        }

        return $machine['id'];
    }

    /**
     * Check if a particular machine record already exists.
     *
     * This method checks whether the given machine already exists in the database. It doesn't search with the id, but
     * with the following fields: "email"
     *
     * @param array $machine Associative array with the machine's data. Each key has the same name with the database
     * fields.
     *
     * @return bool Returns whether the record exists or not.
     *
     * @throws Exception If machine email property is missing.
     */
    public function exists($machine)
    {
        if ( ! isset($machine['email']))
        {
            throw new Exception('Machine\'s email is not provided.');
        }

        // This method shouldn't depend on another method of this class.
        $num_rows = $this->db
            ->select('*')
            ->from('ea_machines')
            ->join('ea_roles', 'ea_roles.id = ea_machines.id_roles', 'inner')
            ->where('ea_machines.email', $machine['email'])
            ->where('ea_roles.slug', DB_SLUG_MACHINE)
            ->get()->num_rows();

        return ($num_rows > 0) ? TRUE : FALSE;
    }

    /**
     * Insert a new machine record to the database.
     *
     * @param array $machine Associative array with the machine's data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the id of the new record.
     *
     * @throws Exception If machine record could not be inserted.
     */
    protected function _insert($machine)
    {
        // Before inserting the machine we need to get the machine's role id
        // from the database and assign it to the new record as a foreign key.
        $machine_role_id = $this->db
            ->select('id')
            ->from('ea_roles')
            ->where('slug', DB_SLUG_MACHINE)
            ->get()->row()->id;

        $machine['id_roles'] = $machine_role_id;

        if ( ! $this->db->insert('ea_machines', $machine))
        {
            throw new Exception('Could not insert machine to the database.');
        }

        return (int)$this->db->insert_id();
    }

    /**
     * Update an existing machine record in the database.
     *
     * The machine data argument should already include the record ID in order to process the update operation.
     *
     * @param array $machine Associative array with the machine's data. Each key has the same name with the database
     * fields.
     *
     * @return int Returns the updated record ID.
     *
     * @throws Exception If machine record could not be updated.
     */
    protected function _update($machine)
    {
        // Do not update empty string values.
        foreach ($machine as $key => $value)
        {
            if ($value === '')
            {
                unset($machine[$key]);
            }
        }

        $this->db->where('id', $machine['id']);
        if ( ! $this->db->update('ea_machines', $machine))
        {
            throw new Exception('Could not update machine to the database.');
        }

        return (int)$machine['id'];
    }

    /**
     * Find the database id of a machine record.
     *
     * The machine data should include the following fields in order to get the unique id from the database: "email"
     *
     * IMPORTANT: The record must already exists in the database, otherwise an exception is raised.
     *
     * @param array $machine Array with the machine data. The keys of the array should have the same names as the
     * database fields.
     *
     * @return int Returns the ID.
     *
     * @throws Exception If machine record does not exist.
     */
    public function find_record_id($machine)
    {
        if ( ! isset($machine['email']))
        {
            throw new Exception('machine\'s email was not provided: '
                . print_r($machine, TRUE));
        }

        // Get machine's role id
        $result = $this->db
            ->select('ea_machines.id')
            ->from('ea_machines')
            ->join('ea_roles', 'ea_roles.id = ea_machines.id_roles', 'inner')
            ->where('ea_machines.email', $machine['email'])
            ->where('ea_roles.slug', DB_SLUG_MACHINE)
            ->get();

        if ($result->num_rows() == 0)
        {
            throw new Exception('Could not find machine record id.');
        }

        return $result->row()->id;
    }

    /**
     * Validate machine data before the insert or update operation is executed.
     *
     * @param array $machine Contains the machine data.
     *
     * @return bool Returns the validation result.
     *
     * @throws Exception If machine validation fails.
     */
    public function validate($machine)
    {
        $this->load->helper('data_validation');
        require __DIR__ . '/../FirePHPCore/FirePHP.class.php';
        $f = new FirePHP();
        $f -> log($machine);
        // If a machine id is provided, check whether the record
        // exist in the database.
        if (isset($machine['id']))
        {
            $num_rows = $this->db->get_where('ea_machines',
                ['id' => $machine['id']])->num_rows();
            if ($num_rows == 0)
            {
                throw new Exception('Provided machine id does not '
                    . 'exist in the database.');
            }
        }
        // Validate required fields
        // if ( ! isset($machine['last_name'])
        //     || ! isset($machine['email'])
        //     || ! isset($machine['phone_number']))
        // {
        //     throw new Exception('Not all required fields are provided: '
        //         . print_r($machine, TRUE));
        // }

        // Validate email address
        if ( ! filter_var($machine['email'], FILTER_VALIDATE_EMAIL))
        {
            throw new Exception('Invalid email address provided: '
                . $machine['email']);
        }

        // When inserting a record the email address must be unique.
        $machine_id = (isset($machine['id'])) ? $machine['id'] : '';

        $num_rows = $this->db
            ->select('*')
            ->from('ea_machines')
            ->join('ea_roles', 'ea_roles.id = ea_machines.id_roles', 'inner')
            ->where('ea_roles.slug', DB_SLUG_MACHINE)
            ->where('ea_machines.email', $machine['email'])
            ->where('ea_machines.id <>', $machine_id)
            ->get()
            ->num_rows();

        if ($num_rows > 0)
        {
            throw new Exception('Given email address belongs to another machine record. '
                . 'Please use a different email.');
        }

        return TRUE;
    }

    /**
     * Delete an existing machine record from the database.
     *
     * @param int $machine_id The record id to be deleted.
     *
     * @return bool Returns the delete operation result.
     *
     * @throws Exception If $machine_id argument is invalid.
     */
    public function delete($machine_id)
    {
        if ( ! is_numeric($machine_id))
        {
            throw new Exception('Invalid argument type $machine_id: ' . $machine_id);
        }

        $num_rows = $this->db->get_where('ea_machines', ['id' => $machine_id])->num_rows();
        if ($num_rows == 0)
        {
            return FALSE;
        }

        return $this->db->delete('ea_machines', ['id' => $machine_id]);
    }

    /**
     * Get a specific row from the appointments table.
     *
     * @param int $machine_id The record's id to be returned.
     *
     * @return array Returns an associative array with the selected record's data. Each key has the same name as the
     * database field names.
     *
     * @throws Exception If $machine_id argumnet is invalid.
     */
    public function get_row($machine_id)
    {
        if ( ! is_numeric($machine_id))
        {
            throw new Exception('Invalid argument provided as $machine_id : ' . $machine_id);
        }

   

        return $this->db->get_where('ea_machines', ['id' => $machine_id])->row_array();
    }

    /**
     * Get a specific field value from the database.
     *
     * @param string $field_name The field name of the value to be returned.
     * @param int $machine_id The selected record's id.
     *
     * @return string Returns the records value from the database.
     *
     * @throws Exception If $machine_id argument is invalid.
     * @throws Exception If $field_name argument is invalid.
     * @throws Exception If requested machine record does not exist in the database.
     * @throws Exception If requested field name does not exist in the database.
     */
    public function get_value($field_name, $machine_id)
    {
        if ( ! is_numeric($machine_id))
        {
            throw new Exception('Invalid argument provided as $machine_id: '
                . $machine_id);
        }

        if ( ! is_string($field_name))
        {
            throw new Exception('$field_name argument is not a string: '
                . $field_name);
        }

        if ($this->db->get_where('ea_machines', ['id' => $machine_id])->num_rows() == 0)
        {
            throw new Exception('The record with the $machine_id argument '
                . 'does not exist in the database: ' . $machine_id);
        }

        $row_data = $this->db->get_where('ea_machines', ['id' => $machine_id]
        )->row_array();
        if ( ! isset($row_data[$field_name]))
        {
            throw new Exception('The given $field_name argument does not'
                . 'exist in the database: ' . $field_name);
        }

        $machine = $this->db->get_where('ea_machines', ['id' => $machine_id])->row_array();

        return $machine[$field_name];
    }

    /**
     * Get all, or specific records from appointment's table.
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
        

        $machines_role_id = $this->get_machines_role_id();

        if ($where_clause != '')
        {
            $this->db->where($where_clause);
        }

        $this->db->where('id_roles', $machines_role_id);

        return $this->db->get('ea_machines')->result_array();
    }

    /**
     * Get the machines role id from the database.
     *
     * @return int Returns the role id for the machine records.
     */
    public function get_machines_role_id()
    {
        return $this->db->get_where('ea_roles', ['slug' => DB_SLUG_MACHINE])->row()->id;
    }
}
