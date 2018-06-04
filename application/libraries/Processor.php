<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Processor {

    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function checkLogin()
    {
        if(!$this->CI->session->userdata('isLogin')) {
            redirect('');
        }
    }

    public function saveProfile($id, $data)
    {
        return $this->CI->users->saveProfile($id, $data);
    }

    public function getDateAxis($to)
    {
        $date = array();
        $default = date("j M Y", strtotime("1 May 2018"));
        $from = date("j M Y", strtotime("-6 day", strtotime($to)));
        if(strtotime($from) < strtotime($default)) {
            $from = $default;
        }

        while(strtotime($from) <= strtotime($to)) {
            array_push($date, date("j M", strtotime($from)));
            $from = date("j M Y", strtotime("+1 day", strtotime($from)));
        }
        return $date;
    }

    public function getTimeAxis($from, $to)
    {
        $time = array();
        $from = (int)explode(":", $from)[0];
        $to = (int)explode(":", $to)[0];
        for($i=$from;$i<=$to;$i++) {
            if($i<10) {
                $temp = "0$i:00";
            } else {
                $temp = "$i:00";
            }
            array_push($time, $temp);
        }
        return $time;
    }

    public function getNodeUsage($id, $date, $from, $to)
    {
        $usage = array();
        $date = date("Y-m-d", strtotime($date));
        $from = (int)explode(":", $from)[0];
        $to = (int)explode(":", $to)[0];
        for($i=$from;$i<=$to;$i++) {
            $node = $this->CI->datas->getUsage($id, $date, $i);
            array_push($usage, number_format($node, 3));
        }
        return $usage;
    }

    public function getHourlyUsage($userid, $date, $from, $to)
    {
        $total = array();
        $date = date("Y-m-d", strtotime($date));
        $from = (int)explode(":", $from)[0];
        $to = (int)explode(":", $to)[0];
        for($i=$from;$i<=$to;$i++) {
            $usage = $this->CI->datas->getUsageTotalByHour($userid, $date, $i);
            array_push($total, number_format($usage, 3));
        }
        return $total;
    }

    public function getAvgHourlyUsage($location, $date, $from, $to)
    {
        $avg = array();
        $date = date("Y-m-d", strtotime($date));
        $from = (int)explode(":", $from)[0];
        $to = (int)explode(":", $to)[0];
        for($i=$from;$i<=$to;$i++) {
            $usage = $this->CI->datas->getUsageAvgByHour($location, $date, $i);
            array_push($avg, number_format($usage, 3));
        }
        return $avg;
    }

    public function getHourlyProduction($userid, $date, $from, $to)
    {
        $total = array();
        $date = date("Y-m-d", strtotime($date));
        $from = (int)explode(":", $from)[0];
        $to = (int)explode(":", $to)[0];
        for($i=$from;$i<=$to;$i++) {
            $production = $this->CI->datas->getProductionTotalByHour($userid, $date, $i);
            array_push($total, number_format($production, 3));
        }
        return $total;
    }

    public function getAvgHourlyProduction($location, $date, $from, $to)
    {
        $avg = array();
        $date = date("Y-m-d", strtotime($date));
        $from = (int)explode(":", $from)[0];
        $to = (int)explode(":", $to)[0];
        for($i=$from;$i<=$to;$i++) {
            $usage = $this->CI->datas->getProductionAvgByHour($location, $date, $i);
            array_push($avg, number_format($usage, 3));
        }
        return $avg;
    }

    public function getPrice($id, $date, $from, $to)
    {
        $final = array();
        $date = date('N', strtotime($date));
        $from = (int)explode(":", $from)[0];
        $to = (int)explode(":", $to)[0];
        $price = $this->CI->datas->getPrice($id);

        if($date < 6) {
            for($i=$from;$i<=$to;$i++) {
                if(($i>=0 && $i<=6) || ($i>=22 && $i<=23)) {
                    array_push($final, $price['offpeak']);
                } elseif(($i>=7 && $i<=13) || ($i>=20 && $i<=21)) {
                    array_push($final, $price['shoulder']);
                } elseif($i>=14 && $i<=19) {
                    array_push($final, $price['peak']);
                }
            }
        } else {
            for($i=$from;$i<=$to;$i++) {
                if(($i>=0 && $i<=6) || ($i>=22 && $i<=23)) {
                    array_push($final, $price['offpeak']);
                } elseif($i>=7 && $i<=21) {
                    array_push($final, $price['shoulder']);
                }
            }
        }

        return $final;
    }

    public function getHourlyBatteryAct($userid, $date, $from, $to)
    {
        $total = array();
        $date = date("Y-m-d", strtotime($date));
        $from = (int)explode(":", $from)[0];
        $to = (int)explode(":", $to)[0];
        for($i=$from;$i<=$to;$i++) {
            $act = $this->CI->datas->getBatteryActByHour($userid, $date, $i);
            if($act['status'] == 1) {
                $act['amount']*= -1;
            }
            array_push($total, number_format($act['amount'], 3));
        }
        return $total;
    }

    public function getBatterySums($userid, $date)
    {
        $to = date("Y-m-d", strtotime($date));
        $from = date("Y-m-d", strtotime("-6 days", strtotime($date)));
        $sums = $this->CI->datas->getBatterySums($userid, $from, $to);

        foreach($sums as $sum) {
            $sum['date'] = date("j M Y", strtotime($sum['date']));
        }
        return array_reverse($sums);
    }

    public function getAvgBatterySum($location, $to)
    {
        $avg = array();
        $default = date("j M Y", strtotime("1 May 2018"));
        $from = date("j M Y", strtotime("-6 day", strtotime($to)));
        if(strtotime($from) < strtotime($default)) {
            $from = $default;
        }

        while(strtotime($from) <= strtotime($to)) {
            $sum = $this->CI->datas->getAvgBatterySum($location, date("Y-m-d", strtotime($from)));
            if($sum['status'] == 1) {
                $sum['amount']*= -1;
            }
            array_push($avg, number_format($sum['amount'], 3));
            $from = date("j M Y", strtotime("+1 day", strtotime($from)));
        }
        return $avg;
    }

    public function getHourlyVehicleBat($userid, $date, $from, $to)
    {
        $total = array();
        $date = date("Y-m-d", strtotime($date));
        $from = (int)explode(":", $from)[0];
        $to = (int)explode(":", $to)[0];
        $capacity = $this->getVehicle($userid)['capacity'];
        for($i=$from;$i<=$to;$i++) {
            $amount = $this->CI->datas->getVehicleBatByHour($userid, $date, $i);
            $final = ($amount/$capacity) * 100;
            array_push($total, number_format($final, 2));
        }
        return $total;
    }

    public function getNode($id)
    {
        return $this->CI->datas->getNode($id);
    }

    public function getNodes($userid)
    {
        return $this->CI->datas->getNodes($userid);
    }

    public function saveNode($data)
    {
        if($data['id'] == "new") {
            unset($data['id']);
            return $this->CI->datas->insertNode($data);
        } else {
            return $this->CI->datas->updateNode($data);
        }
    }

    public function saveNodes($userid, $status)
    {
        $nodes = $this->CI->datas->getNodes($userid);
        foreach($nodes as &$node) {
            if($status == null) {
                $node['status'] = 0;
            } else {
                if(in_array($node['id'], $status)) {
                    $node['status'] = 1;
                } else {
                    $node['status'] = 0;
                }
            }
        }
        return $this->CI->datas->updateNodes($nodes);
    }

    public function getVehicle($userid)
    {
        return $this->CI->datas->getVehicle($userid);
    }

    public function saveVehicle($data)
    {
        return $this->CI->datas->updateVehicle($data);
    }

    public function getBattery($userid)
    {
        return $this->CI->datas->getBattery($userid);
    }

    public function saveBattery($data)
    {
        return $this->CI->datas->updateBattery($data);
    }

    public function getSolar($userid)
    {
        return $this->CI->datas->getSolar($userid);
    }

    public function saveSolar($data)
    {
        return $this->CI->datas->updateSolar($data);
    }

    public function getLocation($userid)
    {
        return $this->CI->datas->getLocation($userid);
    }
}