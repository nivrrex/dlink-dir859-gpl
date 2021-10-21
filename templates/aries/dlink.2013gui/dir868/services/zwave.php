<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/phyinf.php";

//-----------------------------------------------------------------------------
// < Transfer function >
//
//-----------------------------------------------------------------------------
    // Find path.
    function is_path_exist($path)
    {
        $counter=0;
        foreach($path)
        {
            $counter++;
        }
        return $counter;
    }

    // parse string to lower case and replace ' ' as '_'
    function parsing_name($string_name)
    {
        $count=cut_count($string_name,' ');
        if($count>1)
        {
            $temp=cut($string_name,0,' ');
            $index=1;
            while($index<$count)
            {
                $temp=$temp.'_'.cut($string_name,$index,' ');
                $index++;
            }
            return tolower($temp);
        }else{
           return tolower($string_name);
       }
    }

    // Find ept_info index.
    function get_ept_info_index($temp_ept_info, $temp_type)
    {
        $index=0;
        $runtime_path="/runtime/end_point/entry";
        foreach($runtime_path)
        {
            $index++;
            $query_ept_info=query($runtime_path.':'.$index.'/ept_info');
            $query_type=query($runtime_path.':'.$index.'/type');
            if($temp_ept_info==$query_ept_info && $temp_type==$query_type)
            {
                return $index;
            }
        }
        return 0;
    }

    // Find uid index in flash.
    function get_uid_index_in_flash($temp_uid)
    {
        $index=0;
        $path="/end_point/entry";
        foreach($path)
        {
            $index++;
            $query_uid=query($path.':'.$index.'/uid');
            if($temp_uid==$query_uid)
            {
                return $index;
            }
        }
        return 0;
    }

    // Find uid index in runtime.
    function get_uid_index($temp_uid)
    {
        $index=0;
        $runtime_path="/runtime/end_point/entry";
        foreach($runtime_path)
        {
            $index++;
            $query_uid=query($runtime_path.':'.$index.'/uid');
            if($temp_uid==$query_uid)
            {
                return $index;
            }
        }
        return 0;
    }

//-----------------------------------------------------------------------------
// <PHP_ACTION=add_node_value>
//
//-----------------------------------------------------------------------------
    if($_GLOBALS["PHP_ACTION"]=="add_node_value")
    {
        $runtime_path="/runtime/end_point/entry";
        $index=get_ept_info_index($ept_info, $type);
        echo 'echo add_node_value:return value='.$index.'> /dev/console\n';

        if($index>0) // Update node data.
        {
            set($runtime_path.':'.$index."/opstatus",$opstatus);
            set($runtime_path.':'.$index."/nickname",$nickname);
            set($runtime_path.':'.$index."/type",$type);
            set($runtime_path.':'.$index."/manufacturer",$manufacturer);
            set($runtime_path.':'.$index."/product_id",$product_id);
            set($runtime_path.':'.$index."/ept_info",$ept_info);
        }
        else // Add a new node and Search max uid.
        {
            $index=0;
            $max_uid=0;
            $search_uid=0;
            $count_entry=0;
            $new_uid="EPT-";

            if(query($runtime_path.':1/uid')=='')
            {
                $new_uid=$new_uid."1";
                $index=1;
            }
            else
            {
                foreach($runtime_path) // Find max uid number.
                {
                    $index++;
                    $query_value=query($runtime_path.':'.$index.'/uid');
                    $search_uid=scut($query_value,0,'EPT-');
                    if( $search_uid>$max_uid )
                    {
                        $max_uid=$search_uid;
                    }
                }
                $count_entry=$index;
                $index=$index+1;
                $max_uid=$max_uid+1;
                $new_uid=$new_uid.$max_uid;
            }

            set($runtime_path.':'.$index."/uid",$new_uid);
            set($runtime_path.':'.$index."/opstatus",$opstatus);
            set($runtime_path.':'.$index."/nickname",$nickname);
            set($runtime_path.':'.$index."/type",$type);
            set($runtime_path.':'.$index."/manufacturer",$manufacturer);
            set($runtime_path.':'.$index."/product_id",$product_id);
            set($runtime_path.':'.$index."/ept_info",$ept_info);

            echo 'echo ==Add a new node=='.$new_uid.'> /dev/console\n';

        }
    }

//-----------------------------------------------------------------------------
// <PHP_ACTION = set_node_value>
//
//-----------------------------------------------------------------------------
    if($_GLOBALS["PHP_ACTION"]=="set_node_value")
    {
        echo 'echo set_node_value:'.$ept_info.'> /dev/console\n';

        $runtime_path="/runtime/end_point/entry";
        $index=get_ept_info_index($ept_info, $type);
        if($index>0)
        {
            set($runtime_path.':'.$index.'/'.$label,$value);
        }
    }

//-----------------------------------------------------------------------------
// <PHP_ACTION = delete_node_value>
//
//-----------------------------------------------------------------------------
    if($_GLOBALS["PHP_ACTION"]=="delete_node_value")
    {
        echo 'echo delete_node_value:'.$ept_info.'> /dev/console\n';
        $index=get_ept_info_index($ept_info, $type);
        $runtime_path="/runtime/end_point/entry";
        if($index>0)
        {
            del($runtime_path.':'.$index);
            echo 'echo Delete:'.$ept_info.'> /dev/console\n';
        }
    }

//-----------------------------------------------------------------------------
// <PHP_ACTION = set_private_value>
//
//-----------------------------------------------------------------------------
    if($_GLOBALS["PHP_ACTION"]=="set_private_value")
    {
        //echo 'echo set_private_value,ept_info='.$ept_info.'> /dev/console\n';
        $index=get_ept_info_index($ept_info, $type);

        $runtime_path="/runtime/end_point/entry";
        if($index>0)
        {
            set($runtime_path.':'.$index.'/private/'.parsing_name($label),$value);
        }
    }

//-----------------------------------------------------------------------------
// <PHP_ACTION = delete_private_value>
//
//-----------------------------------------------------------------------------
    if($_GLOBALS["PHP_ACTION"]=="delete_private_value")
    {
        echo 'echo delete_private_value:'.$ept_info.'> /dev/console\n';

        $index=get_ept_info_index($ept_info, $type);
        $runtime_path="/runtime/end_point/entry";
        if($index>0)
        {
            if(is_path_exist($runtime_path.':'.$index.'/private')>0)
            {
                del($runtime_path.':'.$index.'/private');
            }
        }
    }

//-----------------------------------------------------------------------------
// <PHP_ACTION = copy_runtime_data_into_flash>
//
//-----------------------------------------------------------------------------
//    if($_GLOBALS["PHP_ACTION"]=="copy_runtime_data_into_flash")
//    {
//        //echo 'echo copy_runtime_data_into_flash:'.$uid.'> /dev/console\n';
//
//        $runtime_path="/runtime/end_point/entry";
//        $flash_path="/end_point";
//
//        if(is_path_exist($flash_path)==0)
//        {
//            set($flash_path,"");
//        }
//
//        if(get_uid_index_in_flash($uid)==0)
//        {
//            $uid_index=get_uid_index($uid);
//            del($runtime_path.":".$uid_index."/private");
//            mov($runtime_path.":".$uid_index,"/end_point");
//            echo 'mfc save';
//        }
//    }

?>
