<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Script extends CI_Controller {

    public function index()
    {
        if(false) {
            ini_set('max_execution_time', 300);

            $this->purge_user_data();

            for($userid=1;$userid<=3;$userid++) {
                echo "User Id: $userid <br>";
                $this->populate_usage($userid);
                $this->populate_production($userid);
                $this->populate_usage_forecast_today($userid);
                $this->populate_production_forecast_today($userid);
                $this->populate_usage_forecast_tomorrow($userid);
                $this->populate_production_forecast_tomorrow($userid);
                $this->populate_vehicle_act_bat($userid);
                $this->populate_battery_act($userid);
                $this->populate_battery_sum($userid);
            }
        } elseif(false) {
            $this->purge_system_data();

            $this->populate_weather();
        } else {
            echo "No script to run.";
        }
    }

    private function purge_user_data()
    {
        $this->db->empty_table('node_usages');
        $this->db->empty_table('solar_productions');
        $this->db->empty_table('forecast_today');
        $this->db->empty_table('forecast_tomorrow');
        $this->db->empty_table('vehicle_acts');
        $this->db->empty_table('vehicle_bats');
        $this->db->empty_table('battery_acts');
        $this->db->empty_table('battery_sums');
    }

    private function purge_system_data()
    {
        $this->db->empty_table('location_weathers');
    }

	private function populate_usage($userid)
	{
	    $fridge = 0.25;
        $comp = 0.15;
	    $lamp = 0.014;
        $aircon = 3.500;
        $tv = 0.243;
        $ps = 0.14;
		for($i=0;$i<30;$i++) {
		    $d = mktime(0, 0, 0, 5, 1+$i, 2018);
		    $date = date('Y-m-d', $d);
            $weather = $this->db->select('weather')->from('location_weathers')
                ->join('locations', 'location_weathers.locationid = locations.id')
                ->join('users', 'locations.id = users.locationid')
                ->where('users.id', $userid)->where('location_weathers.date', $date)->get()->row_array()['weather'];
		    echo "ADD TO USAGE DATE $date<br>";
			for($hour=0;$hour<24;$hour++) {
			    if($i == 29 && $hour>15) continue;
                echo "ADD USAGE TIME $hour<br>";

                $fridgeid = 1+(6*($userid-1));
                $compid = 2+(6*($userid-1));
                $lampid = 3+(6*($userid-1));
                $airconid = 4+(6*($userid-1));
                $tvid = 5+(6*($userid-1));
                $psid = 6+(6*($userid-1));

                $fridgesch = $this->db->get_where('node_schedules', array('nodeid'=>$fridgeid, 'status'=>0))->row_array();
                $compsch = $this->db->get_where('node_schedules', array('nodeid'=>$compid, 'status'=>0))->row_array();
                $lampsch = $this->db->get_where('node_schedules', array('nodeid'=>$lampid, 'status'=>0))->row_array();
                $airconsch = $this->db->get_where('node_schedules', array('nodeid'=>$airconid, 'status'=>0))->row_array();
                $tvsch = $this->db->get_where('node_schedules', array('nodeid'=>$tvid, 'status'=>0))->row_array();
                $pssch = $this->db->get_where('node_schedules', array('nodeid'=>$psid, 'status'=>0))->row_array();

                if($fridgesch!=null && $fridgesch['start']<=$hour && $hour<=$fridgesch['end']) {
                    $fridgefin = 0;
                } else {
                    if(rand(1,100) <= 50) {
                        $fridgefin = $fridge - ($fridge*rand(1,20)/100);
                    } else {
                        $fridgefin = $fridge + ($fridge*rand(1,20)/100);
                    }
                }
                if($compsch!=null && $compsch['start']<=$hour && $hour<=$compsch['end']) {
                    $compfin = 0;
                } else {
                    if(rand(1,100) <= 50) {
                        $compfin = $comp - ($comp*rand(1,20)/100);
                    } else {
                        $compfin = $comp + ($comp*rand(1,20)/100);
                    }
                }
                if($lampsch!=null && $lampsch['start']<=$hour && $hour<=$lampsch['end']) {
                    $lampfin = 0;
                } else {
                    if(rand(1, 100) <= 50) {
                        $lampfin = $lamp - ($lamp * rand(1, 20) / 100);
                    } else {
                        $lampfin = $lamp + ($lamp * rand(1, 20) / 100);
                    }
                }
                if(0<=$hour && $hour<=18 || $weather == 'Clear' || $weather == 'Cloudy') {
                    $airconfin = 0;
                } else {
                    if($weather == 'Shower') {
                        if(rand(1, 100) <= 50) {
                            $airconfin = $aircon - ($aircon * rand(1, 10) / 100);
                        } else {
                            $airconfin = $aircon + ($aircon * rand(1, 10) / 100);
                        }
                    } else {
                        if(rand(1, 100) <= 50) {
                            $airconfin = $aircon + ($aircon * rand(10, 20) / 100);
                        } else {
                            $airconfin = $aircon + ($aircon * rand(10, 20) / 100);
                        }
                    }
                }
                if($tvsch!=null && $tvsch['start']<=$hour && $hour<=$tvsch['end']) {
                    $tvfin = 0;
                } else {
                    if(rand(1, 100) <= 50) {
                        $tvfin = $tv - ($tv * rand(1, 20) / 100);
                    } else {
                        $tvfin = $tv + ($tv * rand(1, 20) / 100);
                    }
                }
                if($pssch!=null && $pssch['start']<=$hour && $hour<=$pssch['end']) {
                    $psfin = 0;
                } else {
                    if(rand(1, 100) <= 50) {
                        $psfin = $ps - ($ps * rand(1, 20) / 100);
                    } else {
                        $psfin = $ps + ($ps * rand(1, 20) / 100);
                    }
                }

                $this->db->insert('node_usages', array('nodeid'=>$fridgeid, 'date'=>$date, 'time'=>$hour, 'amount'=>round($fridgefin, 3)));
                $this->db->insert('node_usages', array('nodeid'=>$compid, 'date'=>$date, 'time'=>$hour, 'amount'=>round($compfin, 3)));
                $this->db->insert('node_usages', array('nodeid'=>$lampid, 'date'=>$date, 'time'=>$hour, 'amount'=>round($lampfin, 3)));
                $this->db->insert('node_usages', array('nodeid'=>$airconid, 'date'=>$date, 'time'=>$hour, 'amount'=>round($airconfin, 3)));
                $this->db->insert('node_usages', array('nodeid'=>$tvid, 'date'=>$date, 'time'=>$hour, 'amount'=>round($tvfin, 3)));
                $this->db->insert('node_usages', array('nodeid'=>$psid, 'date'=>$date, 'time'=>$hour, 'amount'=>round($psfin, 3)));
			}
		}
		echo "DONE";
	}

    private function populate_usage_forecast_today($userid)
    {
        $date = date("Y-m-d", strtotime("30 May 2018"));
        $avg = $this->db->select_sum('amount')->from('node_usages')
            ->join('nodes', 'node_usages.nodeid = nodes.id')->join('users', 'nodes.userid = users.id')
            ->where('users.id', $userid)->where('date', $date)
            ->get()->row_array()['amount'] / 16;
        $usage = $this->db->select_sum('amount')->from('node_usages')
            ->join('nodes', 'node_usages.nodeid = nodes.id')->join('users', 'nodes.userid = users.id')
            ->where('users.id', $userid)->where('date', $date)->where('time', 15)
            ->get()->row_array()['amount'];

        for($hour=16;$hour<24;$hour++) {
            echo "ADD USAGE FORECAST TODAY TIME $hour<br>";

            $avgfin = $avg * rand(1,20)/100;
            if(rand(1, 100) <= 50) {
                $final = $usage - $avgfin;
            } else {
                $final = $usage + $avgfin;
            }
            $this->db->insert('forecast_today', array('userid'=>$userid, 'time'=>$hour, 'status'=>1, 'amount'=>round($final, 3)));
        }
    }

	private function populate_usage_forecast_tomorrow($userid)
    {
        $date = date("Y-m-d", strtotime("29 May 2018"));
        $avg = $this->db->select_avg('amount')->from('node_usages')
            ->join('nodes', 'node_usages.nodeid = nodes.id')->join('users', 'nodes.userid = users.id')
            ->where('users.id', $userid)->where('date', $date)
            ->get()->row_array()['amount'];

        for($hour=0;$hour<24;$hour++) {
            echo "ADD USAGE FORECAST TOMORROW TIME $hour<br>";
            $usage = $this->db->select_sum('amount')->from('node_usages')
                ->join('nodes', 'node_usages.nodeid = nodes.id')->join('users', 'nodes.userid = users.id')
                ->where('users.id', $userid)->where('date', $date)->where('time', $hour)
                ->get()->row_array()['amount'];

            $avgfin = $avg * rand(1,20)/100;
            if(rand(1, 100) <= 50) {
                $final = $usage - $avgfin;
            } else {
                $final = $usage + $avgfin;
            }
            $this->db->insert('forecast_tomorrow', array('userid'=>$userid, 'time'=>$hour, 'status'=>1, 'amount'=>round($final, 3)));
        }
    }

	private function populate_production($userid)
    {
        $base = 0;
        if($userid == 1) {
            $base = 6.000;
        } elseif($userid == 2) {
            $base = 4.000;
        } elseif($userid == 3) {
            $base = 2.000;
        }
        for($i=0;$i<30;$i++) {
            $d = mktime(0, 0, 0, 5, 1+$i, 2018);
            $date = date('Y-m-d', $d);
            $weather = $this->db->select('weather')->from('location_weathers')
                ->join('locations', 'location_weathers.locationid = locations.id')
                ->join('users', 'locations.id = users.locationid')
                ->where('users.id', $userid)->where('location_weathers.date', $date)->get()->row_array()['weather'];
            echo "ADD TO PRODUCTION DATE $date<br>";
            for($hour=0;$hour<24;$hour++) {
                if($i == 29 && $hour>15) continue;
                echo "ADD PRODUCTION TIME $hour<br>";

                if($hour<=7 || $hour>=18) {
                    $final = 0;
                } else {
                    if(rand(1,100) <= 50) {
                        $final = $base - ($base*rand(1,20)/100);
                    } else {
                        $final = $base + ($base*rand(1,20)/100);
                    }
                }
                if($weather == "Cloudy") {
                    $final *= 90/100;
                } elseif($weather == "Shower") {
                    $final *= 70/100;
                } elseif($weather == "Rain") {
                    $final *= 50/100;
                }
                $this->db->insert('solar_productions', array('solarid'=>$userid, 'date'=>$date, 'time'=>$hour, 'amount'=>round($final, 3)));
            }
        }
        echo "DONE";
    }

    private function populate_production_forecast_today($userid)
    {
        $date = date("Y-m-d", strtotime("30 May 2018"));
        $avg = $this->db->select_avg('amount')->from('solar_productions')
            ->join('solars', 'solar_productions.solarid = solars.id')->join('users', 'solars.userid = users.id')
            ->where('users.id', $userid)->where('date', $date)
            ->get()->row_array()['amount'];
        $production = $this->db->select('amount')->from('solar_productions')
            ->join('solars', 'solar_productions.solarid = solars.id')->join('users', 'solars.userid = users.id')
            ->where('users.id', $userid)->where('date', $date)->where('time', 15)
            ->get()->row_array()['amount'];
        $weather = $this->db->select('weather')->from('location_weathers')
            ->join('locations', 'location_weathers.locationid = locations.id')
            ->join('users', 'locations.id = users.locationid')
            ->where('users.id', $userid)->where('location_weathers.date', $date)->get()->row_array()['weather'];

        for($hour=16;$hour<24;$hour++) {
            echo "ADD USAGE FORECAST TODAY TIME $hour<br>";

            $avgfin = $avg * rand(1,20)/100;
            if($hour<=7 || $hour>=18) {
                $final = 0;
            } else {
                if(rand(1, 100) <= 50) {
                    $final = $production - $avgfin;
                } else {
                    $final = $production + $avgfin;
                }
            }
            if($weather == "Cloudy") {
                $final *= 90/100;
            } elseif($weather == "Shower") {
                $final *= 70/100;
            } elseif($weather == "Rain") {
                $final *= 50/100;
            }
            $this->db->insert('forecast_today', array('userid'=>$userid, 'time'=>$hour, 'status'=>2, 'amount'=>round($final, 3)));
        }
    }

    private function populate_production_forecast_tomorrow($userid)
    {
        $weather = $this->db->select('weather')->from('location_weathers')
            ->join('locations', 'location_weathers.locationid = locations.id')
            ->join('users', 'locations.id = users.locationid')
            ->where('users.id', $userid)->where('location_weathers.date', date("Y-m-d", strtotime("31 May 2018")))
            ->get()->row_array()['weather'];
        $date = $this->db->select('date')->from('location_weathers')
            ->join('locations', 'location_weathers.locationid = locations.id')
            ->join('users', 'locations.id = users.locationid')
            ->where('users.id', $userid)->where('location_weathers.date <', '2018-05-30')
            ->where('location_weathers.weather', $weather)->order_by('location_weathers.date', 'DESC')
            ->get()->row_array()['date'];
        $avg = $this->db->select_avg('amount')->from('solar_productions')
            ->join('solars', 'solar_productions.solarid = solars.id')->join('users', 'solars.userid = users.id')
            ->where('users.id', $userid)->where('date', $date)
            ->get()->row_array()['amount'];

        for($hour=0;$hour<24;$hour++) {
            echo "ADD PRODUCTION FORECAST TOMORROW TIME $hour<br>";
            $production = $this->db->select('amount')->from('solar_productions')
                ->join('solars', 'solar_productions.solarid = solars.id')->join('users', 'solars.userid = users.id')
                ->where('users.id', $userid)->where('date', $date)->where('time', $hour)
                ->get()->row_array()['amount'];

            $avgfin = $avg * rand(1,20)/100;
            if($hour<=7 || $hour>=18) {
                $final = 0;
            } else {
                if(rand(1, 100) <= 50) {
                    $final = $production - $avgfin;
                } else {
                    $final = $production + $avgfin;
                }
            }
            $this->db->insert('forecast_tomorrow', array('userid'=>$userid, 'time'=>$hour, 'status'=>2, 'amount'=>round($final, 3)));
        }
    }

    private function populate_vehicle_act_bat($userid)
    {
        $full = 30.000;
        $empty = 0.000;
        $use = 3.000;
        $stdev = 0.500;
        $charge = 4.000;
        $total = $full;
        for($i=0;$i<30;$i++) {
            $d = mktime(0, 0, 0, 5, 1+$i, 2018);
            $date = date('Y-m-d', $d);
            echo "ADD TO VEHICLE ACT DATE $date<br>";
            for($hour=0;$hour<24;$hour++) {
                if($i == 29 && $hour>15) continue;
                echo "ADD VEHICLE ACT TIME $hour<br>";

                if($hour==8 || $hour==18) {
                    if(rand(1,100) <= 50) {
                        $final = $use - ($stdev*rand(1,100)/100);
                    } else {
                        $final = $use + ($stdev*rand(1,100)/100);
                    }
                    $status = 1;
                    $total = $total - $final;

                    if($total < $empty) {
                        $surplus = $empty - $total;
                        $final -= $surplus;
                        $total = $empty;
                    }
                    $final = round($final,3);
                    $this->db->insert('vehicle_acts', array('vehicleid'=>$userid, 'date'=>$date, 'time'=>$hour, 'status'=>$status, 'amount'=>$final));
                } elseif($hour>=18 && $hour<=23 && $total!=$full) {
                    if(rand(1,100) <= 50) {
                        $final = $charge - ($charge*rand(1,50)/100);
                    } else {
                        $final = $charge + ($charge*rand(1,50)/100);
                    }
                    $status = 2;
                    $total = $total + $final;

                    if($total > $full) {
                        $surplus = $total - $full;
                        $final -= $surplus;
                        $total = $full;
                    }
                    $final = round($final,3);
                    $this->db->insert('vehicle_acts', array('vehicleid'=>$userid, 'date'=>$date, 'time'=>$hour, 'status'=>$status, 'amount'=>$final));
                }

                $total = round($total,3);
                $this->db->insert('vehicle_bats', array('vehicleid'=>$userid, 'date'=>$date, 'time'=>$hour, 'amount'=>$total));
            }
        }
        echo "DONE";
    }

    private function populate_battery_act($userid)
    {
        for($i=0;$i<30;$i++) {
            $d = mktime(0, 0, 0, 5, 1+$i, 2018);
            $date = date('Y-m-d', $d);
            echo "ADD TO BATTERY ACT DATE $date<br>";
            for($hour=0;$hour<24;$hour++) {
                if($i == 29 && $hour>15) continue;
                echo "ADD BATTERY ACT TIME $hour<br>";
                $production = $this->db->select('amount')->get_where('solar_productions', array('date'=>$date, 'time'=>$hour))->row_array()['amount'];
                $usage = $this->db->select_sum('amount')->get_where('node_usages', array('date'=>$date, 'time'=>$hour))->row_array()['amount'];
                $vehicle = $this->db->select_sum('amount')->get_where('vehicle_acts', array('date'=>$date, 'time'=>$hour, 'status'=>2))->row_array()['amount'];
                $total = $production - $usage - $vehicle;

                if($total < 0) {
                    $status = 1;
                    $final = $total * -1;
                } else {
                    $status = 2;
                    $final = $total;
                }
                $final = round($final,3);
                $this->db->insert('battery_acts', array('batteryid'=>$userid, 'date'=>$date, 'time'=>$hour, 'status'=>$status, 'amount'=>$final));
            }
        }
        echo "DONE";
    }

    private function populate_battery_sum($userid)
    {
        for($i=0;$i<29;$i++) {
            $d = mktime(0, 0, 0, 5, 1+$i, 2018);
            $date = date('Y-m-d', $d);
            echo "ADD TO BATTERY SUM DATE $date<br>";
            $production = $this->db->select_sum('amount')->get_where('battery_acts', array('date'=>$date, 'status'=>2))->row_array()['amount'];
            $usage = $this->db->select_sum('amount')->get_where('battery_acts', array('date'=>$date, 'status'=>1))->row_array()['amount'];
            $production = round($production,3);
            $usage = round($usage,3);
            $total = $production - $usage;

            if($total < 0) {
                $status = 1;
                $final = $total * -1.000;
            } else {
                $status = 2;
                $final = $total;
            }
            $this->db->insert('battery_sums', array('batteryid'=>$userid, 'date'=>$date, 'status'=>$status, 'amount'=>$final));
        }
        echo "DONE";
    }

    private function populate_weather()
    {
        for($i=0;$i<31;$i++) {
            $d = mktime(0, 0, 0, 5, 1+$i, 2018);
            $date = date('Y-m-d', $d);
            echo "ADD TO WEATHER DATE $date<br>";
            $rand = rand(1,100);
            if($rand <= 40) {
                $weather = 'Clear';
            } elseif($rand <= 60) {
                $weather = 'Cloudy';
            } elseif($rand <= 80) {
                $weather = 'Shower';
            } else {
                $weather = 'Rain';
            }
            $this->db->insert('location_weathers', array('locationid'=>1, 'date'=>$date, 'weather'=>$weather));
            $this->db->insert('location_weathers', array('locationid'=>2, 'date'=>$date, 'weather'=>$weather));
            $this->db->insert('location_weathers', array('locationid'=>3, 'date'=>$date, 'weather'=>$weather));
            $this->db->insert('location_weathers', array('locationid'=>4, 'date'=>$date, 'weather'=>$weather));
            $this->db->insert('location_weathers', array('locationid'=>5, 'date'=>$date, 'weather'=>$weather));
        }
    }
}