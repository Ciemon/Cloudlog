<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Map extends CI_Controller {

	function index()
	{

        // Calculate Lat/Lng from Locator to use on Maps
        if($this->session->userdata('user_locator')) {
            $this->load->library('qra');

            $qra_position = $this->qra->qra2latlong($this->session->userdata('user_locator'));
            $data['qra'] = "set";
            $data['qra_lat'] = $qra_position[0];
            $data['qra_lng'] = $qra_position[1];
        } else {
            $data['qra'] = "none";
        }

        $this->load->model('Stations');
        $station_id = $this->Stations->find_active();
        $station_data = $this->Stations->profile_clean($station_id);

        // load the view
        $data['station_profile'] = $station_data;
		$data['page_title'] = "Map QSOs";

		$this->load->view('interface_assets/header', $data);
		$this->load->view('map/qsos');
		$this->load->view('interface_assets/footer');
    }

    function custom()
	{
		$this->load->model('bands');
        $this->load->model('modes');

        $data['worked_bands'] = $this->bands->get_worked_bands(); // Used in the view for band select
		$data['modes'] = $this->modes->active(); 					// Used in the view for mode select

        if ($this->input->post('band') != NULL) {   			// Band is not set when page first loads.
            if ($this->input->post('band') == 'All') {          // Did the user specify a band? If not, use all bands
                $bands = $data['worked_bands'];
            }
            else {
                $bands[] = $this->input->post('band');
            }
        }
        else {
            $bands = $data['worked_bands'];
        }

        $data['bands'] = $bands; // Used for displaying selected band(s) in the table in the view

        // Calculate Lat/Lng from Locator to use on Maps
        if($this->session->userdata('user_locator')) {
            $this->load->library('qra');

            $qra_position = $this->qra->qra2latlong($this->session->userdata('user_locator'));
            $data['qra'] = "set";
            $data['qra_lat'] = $qra_position[0];
            $data['qra_lng'] = $qra_position[1];
        } else {
            $data['qra'] = "none";
        }

		$CI =& get_instance();
		$CI->load->model('logbooks_model');
		$result = $CI->logbooks_model->logbook($this->session->userdata('active_station_logbook'))->result();
		
		if ($result) {
			$logbook_name = $result[0]->logbook_name;
		} else {
			$logbook_name = '';
		}

        // load the view
        $data['logbook_name'] = $logbook_name;
		$data['page_title'] = "Map QSOs";

        $data['date_from'] = date('Y-m-d');
        $data['date_to'] = date('Y-m-d', strtotime($data['date_from'].' +1days'));

		$this->load->view('interface_assets/header', $data);
		$this->load->view('map/custom_date');
		$this->load->view('interface_assets/footer');
    }

	// Generic fonction for return Json for MAP //
	public function map_plot_json() {
		$this->load->model('Stations');
		$this->load->model('logbook_model');
		
		// set informations //
		if ($this->input->post('isCustom') == true) {
			$date_from = xss_clean($this->input->post('date_from'));
			$date_to = xss_clean($this->input->post('date_to'));
			$band = xss_clean($this->input->post('band'));
			$mode = xss_clean($this->input->post('mode'));
			$prop_mode = xss_clean($this->input->post('prop_mode'));
			$qsos = $this->logbook_model->map_custom_qsos($date_from, $date_to, $band, $mode, $prop_mode);
		} else if ($this->input->post('isFull') == true) {
            $station_id = $this->Stations->find_active();
			$qsos = $this->logbook_model->get_qsos(null,null,array($station_id)); // no limit for full //
		} else {
			$nb_qso = (intval($this->input->post('nb_qso'))>0)?xss_clean($this->input->post('nb_qso')):18;
			$offset = (intval($this->input->post('offset'))>0)?xss_clean($this->input->post('offset')):null;
			$qsos = $this->logbook_model->get_qsos($nb_qso, $offset);
		}
		// [PLOT] ADD plot //
		$plot_array = $this->logbook_model->get_plot_array_for_map($qsos->result());
		// [MAP Custom] ADD Station //
		$station_array = $this->Stations->get_station_array_for_map();
		
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(array_merge($plot_array, $station_array));
	}

}
