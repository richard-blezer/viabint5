<?php
if($this->p_id>0) {
    $include_script="<script src='https://content.exorbyte.com/demo/".$this->mm_project_name."_".$this->p_id."/exo-loader.js' type=\"text/javascript\"></script>";      
    echo "
     <!--[if !(IE 6)]><!-->
	      <script src='https://content.exorbyte.com/sn/lib/jquery-loader.js' type=\"text/javascript\"></script>
	      <script src='https://content.exorbyte.com/sn/lib/jquery-1.4.2.min.js' type=\"text/javascript\"></script>
        ".$include_script."
	   <!--<![endif]-->     
    ";
}

?>
