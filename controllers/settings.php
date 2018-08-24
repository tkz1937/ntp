<?php

/**
 * NTP settings controller.
 *
 * @category   apps
 * @package    ntp
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/ntp/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * NTP settings controller.
 *
 * @category   apps
 * @package    ntp
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/ntp/
 */

class Settings extends ClearOS_Controller
{
    /**
     * NTP settings controller.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('base');
        $this->load->library('ntp/NTP');

        // Load view data
        //---------------

        try {
            $data['servers'] = $this->ntp->get_servers();
            $data['thanks'] = (preg_match('/clearsdn\.com/', implode($data['servers']))) ? TRUE : FALSE;
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load views
        //-----------

        $this->page->view_form('ntp/settings', $data, lang('base_settings'));
    }

    function edit_view($server)
    {
        $this->lang->load('base');
        $this->load->library('ntp/NTP');
        $data['server'] = $server; 
        // Load view data
        //---------------

        // try {
        //     $data['servers'] = $this->ntp->get_servers();
        //     $data['thanks'] = (preg_match('/clearsdn\.com/', implode($data['servers']))) ? TRUE : FALSE;
        // } catch (Exception $e) {
        //     $this->page->view_exception($e);
        //     return;
        // }
        $this->page->view_form("edit",$data);
    }
    function edit()
    {
        $server = $this->input->post('server');
        $oldserver = $this->input->post('old_server_name');
        // var_dump($oldserver);die;
        if($server != "")
        {
            $fichier = fopen("/etc/ntp.conf", "r"); 
            $server_line = "server ".$oldserver;
            try {
                $temp = new SplFileObject("/etc/tamp.conf", "w");  
                while(!feof($fichier))
            {
                $j = fgets($fichier);
                if ($j != "#" and trim($j) == $server_line) {
                    $text = "server ".$server;
                    $temp->fwrite($text."\n");
                }
                else
                {
                    $temp->fwrite($j);
                }   
            }
            if (unlink("/etc/ntp.conf")) {
                rename("/etc/tamp.conf","/etc/ntp.conf");
            }      
            fclose($fichier);
            redirect('ntp/settings');
        } 
        catch (Exception $e) {
            echo $e;
             redirect('ntp/settings');
        }    
            redirect('ntp/settings');
        }
        else
        {
            $this->page->view_form('edit');
        }
    }

    function delete($server)
    {
        $fichier = fopen("/etc/ntp.conf", "r"); 
        $server_line = "server ".$server;
        try {
            $temp = new SplFileObject("/etc/tamp.conf", "w");  
            while(!feof($fichier))
        {
            $j = fgets($fichier);
            if ($j != "#" and trim($j) == $server_line) {
                # code...
                $text = $j;
            }
            else
            {
                $temp->fwrite($j);
            }
            
        }
        if (unlink("/etc/ntp.conf")) {
            rename("/etc/tamp.conf","/etc/ntp.conf");
        }      
        fclose($fichier);
         redirect('ntp/settings');
        } 
        catch (Exception $e) {
            echo $e;
             redirect('ntp/settings');
        }    
    }
    function add(){
        $this->load->library('ntp/NTP');
        $servers = $this->ntp->get_servers();
        $server_not_exist = true;
        $server_name=$this->input->post('server');
        foreach ($servers as $server) {
            if ($server == trim($server_name) ) {
                $server_not_exist = false;
            }
        }

        if ($server_not_exist) {
            $regex = "((https?|http)://)?"; // SCHEME
            $regex .= "([a-z0-9+!*(),;?&=$_.-]+(:[a-z0-9+!*(),;?&=$_.-]+)?@)?"; // User and Pass
            $regex .= "([a-z0-9\-\.]*)\.(([a-z]{2,4})|([0-9]{1,3}\.([0-9]{1,3})\.([0-9]{1,3})))"; // Host or IP
            $regex .= "(:[0-9]{2,5})?"; // Port
            $regex .= "(/([a-z0-9+$_%-]\.?)+)*/?"; // Path
            $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+/$_.-]*)?"; // GET Query
            $regex .= "(#[a-z_.-][a-z0-9+$%_.-]*)?"; // Anchor
            if (preg_match("~^$regex$~i", $server_name)) {
                $server_line = "server ".trim($server_name);
                $fichier = fopen("/etc/ntp.conf", "a+"); 
                fwrite($fichier, "\n".$server_line);
                redirect('ntp/settings');
            }
         } 

        redirect($this->add_view(1));
    }
    function add_view($error=0){
        $data['error'] = $error;
        $this->page->view_form('add_view',$data);
        
    }
}
