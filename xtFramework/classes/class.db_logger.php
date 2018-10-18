<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Mario Zanier
 * Date: 01.04.14
 * Time: 13:21
 * (c) Mario Zanier, mzanier@xt-commerce.com
 */

class db_logger {


    function __construct() {
        $this->query_array = array();
        $this->query_count = array();
        $this->total_queries = 0;
        $this->total_query_time = 0;
    }

    function addQuery($sql,$time) {

        $this->total_queries+=1;
        $this->total_query_time+=$time;

        $hash = md5($sql);
        $this->query_array[]=time().'|'.$time.'|'.$sql;
        if (isset($this->query_count[$hash])) {
            $this->query_count[$hash]['count']+=1;
            $this->query_count[$hash]['total_time']+=$time;
        } else {
            $this->query_count[$hash]=array('sql'=>$sql,'total_time'=>$time,'count'=>1);
        }
    }
}

