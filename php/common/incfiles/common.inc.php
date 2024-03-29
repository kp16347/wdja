<?php
//****************************************************
// WDJA CMS Power by wdja.cn
// Email: admin@wdja.cn
// Web: http://www.wdja.cn/
//****************************************************
require('const.inc.php');
require('class.inc.php');
require('function.inc.php');
require('ip.inc.php');
require('baidu.inc.php');
require('save_images.inc.php');
require('phpqrcode.php');

function mm_ip_map($ip,$type=0){
  //离线查询IP所属区域信息,国内精确到城市.国外精确到省份.
  ini_set('memory_limit', '2G');
  spl_autoload_register(function ($class)
                        {
                          $class = str_replace("\\","/",$class);
                          if (strpos($class, 'ipip/db') !== FALSE)
                          {
                            require __DIR__.'/ip/'.$class.'.php';
                          }
                        }, true, true);
  $city = new ipip\db\City(__DIR__.'/ip/ipipfree.ipdb');
  if(ii_isnull($ip)) $ip=ii_get_client_ip();
  $ip_array = $city->findMap($ip, 'CN');
  // [country_name] => 中国 [region_name] => 广东 [city_name] => 深圳
  $country = $ip_array['country_name'];
  $region = $ip_array['region_name'];
  $city = $ip_array['city_name'];
  $int = '';
  switch ($type)
  {
    case 0:
      $int = '';
      break;
    case 1:
      $int = '-';
      break;
    case 2:
      $int = '|';
      break;
    case 3:
      $int = '/';
      break;
    case 4:
      $int = '·';
      break;
    default:
      $int = '-';
      break;
  }
  if(ii_isnull($country)) {
  $res = '';
  }elseif(ii_isnull($region)){
  $res = $country;
  }elseif(ii_isnull($city)){
  $res = $country .$int .$region;
  }else{
  $res = $country .$int .$region .$int .$city;
  }
  return $res;

}


function deny_mirrored_websites(){
  //防被代理镜像
  $currentDomain = 'demo.wdja." + "cn';
  $res = '<img style="display:none" src=" " onerror=\'this.onerror=null;var str1="'.$currentDomain.'";str2="docu"+"ment.loca"+"tion.host";str3=eval(str2);if( str1!=str3 ){ do_action = "loca" + "tion." + "href = loca" + "tion.href" + ".rep" + "lace(docu" +"ment"+".loca"+"tion.ho"+"st," + "\"' . $currentDomain .'\"" + ")";eval(do_action) }\' />';
  return $res;
}

function mm_update_field($genre,$id,$field,$val)
{
  //更新任意字段
  global $conn, $variable;
  ii_conn_init();
  $tgenre = $genre;
  $tdatabase = mm_cndatabase(ii_cvgenre($tgenre));
  $tidfield = mm_cnidfield(ii_cvgenre($tgenre));
  $tfpre = mm_cnfpre(ii_cvgenre($tgenre));
  $tsqlstr = 'update '. $tdatabase.' set '.ii_cfnames($tfpre,$field).' = '.$val.' where '.$tidfield.' = ' .$id;
  $trs = ii_conn_query($tsqlstr, $conn);
  if($trs) $res = true;
  else $res = false;
  return $res;
}

function mm_search_field($genre,$field_val,$field,$id = '0')
{
  //查询字段值是否重复
  global $conn, $variable;
  ii_conn_init();
  $res = false;
  $tgenre = $genre;
  $tdatabase = $variable[ii_cvgenre($tgenre) . '.ndatabase'];
  $tidfield = $variable[ii_cvgenre($tgenre) . '.nidfield'];
  $tfpre = $variable[ii_cvgenre($tgenre) . '.nfpre'];
  $tmpstr = '';
  if($id == '0') $tsqlstr = 'select * from '. $tdatabase.' where '.ii_cfnames($tfpre,$field).' = "' .$field_val.'"';
  else $tsqlstr = 'select * from '. $tdatabase.' where '.ii_cfnames($tfpre,$field).' = "' .$field_val.'" and ' . $tidfield . ' <> ' . $id;
  $trs = ii_conn_query($tsqlstr, $conn);
  $trs = ii_conn_fetch_array($trs);
  if($trs) $res = true;
  else $res = false;
  return $res;
}

function mm_get_field($genre,$id,$field)
{
  //获取模块任意字段
  global $conn, $variable;
  ii_conn_init();
  $tgenre = $genre;
  $tdatabase = $variable[ii_cvgenre($tgenre) . '.ndatabase'];
  $tidfield = $variable[ii_cvgenre($tgenre) . '.nidfield'];
  $tfpre = $variable[ii_cvgenre($tgenre) . '.nfpre'];
  $tmpstr = '';
  $tsqlstr = 'select * from '. $tdatabase.' where '.$tidfield.' = ' .$id;
  $trs = ii_conn_query($tsqlstr, $conn);
  $trs = ii_conn_fetch_array($trs);
  return $trs[ii_cfnames($tfpre,$field)];
}

//生成商品编号
function mm_get_shopnum(){
  date_default_timezone_set('PRC');
  return date('ymd').substr(time(),-4).substr(microtime(),2,5).mt_rand(10,99);
}

function alipay_code($imgurl,$price,$orderid,$id){
  $alipay_uid = ii_itake('global.' . ADMIN_FOLDER . '/global:other.alipay_uid','lng');
  if(ii_isnull($alipay_uid)) $alipay_uid = '2088202216609811';
  if(!ii_isnull($imgurl) && !ii_isnull($price) && !ii_isnull($orderid) && !ii_isnull($id)){
    //图片保存位置,价格,订单号,订单ID
    //if(ii_isMobileAgent()) $data = 'alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&amount='. $price.'&userId=2088202216609811&memo='.$orderid;//拼接转账码(金额和备注可编辑)
    //$data = 'alipays://platformapi/startapp?appId=20000123&actionType=scan&biz_data={"s": "money", "u": "'. $alipay_uid .'", "a": "'. $price .'", "m": "'. $orderid .'"}'; //收款码
    $data = 'alipays://platformapi/startapp?appId=20000067&url=http%3A%2F%2F'.$_SERVER['HTTP_HOST'].'%2FopenAlipay.html%3Fmoney%3D'. $price .'%26num%3D'. $orderid;//页面跳转二维码
    $filename = $imgurl.'alipay-code/alipay-'.$price.'-'.$id.'.png'; // 生成的文件名 
    $errorCorrectionLevel = 'H'; // 纠错级别：L、M、Q、H 
    $matrixPointSize = 4; // 点的大小：1到10 
    if (!file_exists($timg)) QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
  }else{
    $filename = ii_itake('global.' . ADMIN_FOLDER . '/global:other.alipay_code','lng');
  }
  return $filename;
}

function alipaycode($imgurl,$price,$orderid,$id){
  $alipay_uid = ii_itake('global.' . ADMIN_FOLDER . '/global:other.alipay_uid','lng');
  if(ii_isnull($alipay_uid)) $alipay_uid = '2088202216609811';
  if(!ii_isnull($imgurl) && !ii_isnull($price) && !ii_isnull($orderid) && !ii_isnull($id)){
    //图片保存位置,价格,订单号,订单ID
    //if(ii_isMobileAgent()) $data = 'alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&amount='. $price.'&userId=2088202216609811&memo='.$orderid;//拼接转账码(金额和备注可编辑)
    $data = 'alipays://platformapi/startapp?appId=20000123&actionType=scan&biz_data={"s": "money", "u": "'. $alipay_uid .'", "a": "'. $price .'", "m": "'. $orderid .'"}'; //收款码
    return urlencode($data);
  }
}

//address
function mm_get_myaddressary($nusername, $lng)
{
  global $conn;
  global $address_database, $address_idfield, $address_fpre;
  $tarys = Array();
  $tusername = ii_get_safecode($nusername);
  $tlng = ii_get_safecode($lng);
  $tsqlstr = "select * from $address_database where " . ii_cfnames($address_fpre, 'username') . "='$tusername' and " . ii_cfnames($address_fpre, 'lng') . "='$tlng' order by " . ii_cfnames($address_fpre, 'time') . " asc";
  $trs = ii_conn_query($tsqlstr, $conn);
  while ($trow = ii_conn_fetch_array($trs))
  {
    $tary[$trow[$address_idfield]]['id'] = $trow[$address_idfield];
    $tary[$trow[$address_idfield]]['name'] = $trow[ii_cfnames($address_fpre, 'name')];
    $tarys += $tary;
  }
  return $tarys;
}

function mm_get_addressary($nusername, $lng)
{
  $tary = mm_get_myaddressary($nusername, $lng);
  $GLOBALS[$tappstr] = $tary;
  return $GLOBALS[$tappstr];
}


function mm_sel_address($nusername,$tid = '0')
{
  global $nusername, $nlng;
  $tary = mm_get_addressary($nusername, $nlng);
  if (is_array($tary))
  {
    $trestr = ii_itake('global.tpl_config.sys_spsort', 'tpl');
    $option_unselected = ii_itake('global.tpl_config.option_unselect', 'tpl');
    $option_selected = ii_itake('global.tpl_config.option_select', 'tpl');
    $tmpstr = '';
    $treturnstr = '';
    foreach ($tary as $key => $val)
    {
      if ($key == $tid) $tmpstr = $option_selected;
      else $tmpstr = $option_unselected;
      $tmpstr = str_replace('{$explain}', $val['name'], $tmpstr);
      $tmpstr = str_replace('{$value}', $val['id'], $tmpstr);
      $treturnstr .= $tmpstr;
    }
    return $treturnstr;
  }
}
//address


function mm_content_mip($content){
  global $nurlpre;
  //以下代码可根据需要修改/删除
  $content = preg_replace('/(width|height)="\d*"\s/', '', $content);//移除图片 width|height
  $content = preg_replace('/ style=\".*?\"/', '',$content);//移除图片 style
  $content = preg_replace('/ class=\".*?\"/', '',$content);//移除图片 class
  //以上代码可根据需要修改/删除
  preg_match_all('/<img (.*?)\>/', $content, $images);
  if(!is_null($images)) {
    foreach($images[1] as $index => $value){
      $mip_img = str_replace(array('src="' ,'<img'),array('src="'.$nurlpre,'<mip-img popup') , $images[0][$index]);//图片绝对路径，根据自己的实际情况选用
      $mip_img = str_replace('>', '></mip-img>', $mip_img);
      $content = str_replace($images[0][$index], $mip_img, $content);
    }
  }
  return $content;
}

function mm_cndatabase($genre, $strers = '')
{
  global $variable;
  if (ii_isnull($strers)) return $variable[$genre . '.ndatabase'];
  else return $variable[$genre . '.ndatabase_' . $strers];
}

function mm_cnidfield($genre, $strers = '')
{
  global $variable;
  if (ii_isnull($strers)) return $variable[$genre . '.nidfield'];
  else return $variable[$genre . '.nidfield_' . $strers];
}

function mm_cnfpre($genre, $strers = '')
{
  global $variable;
  if (ii_isnull($strers)) return $variable[$genre . '.nfpre'];
  else return $variable[$genre . '.nfpre_' . $strers];
}

function mm_cntitle($strers)
{
  global $ntitle,$ngenre;
  if (!ii_isnull($strers)) $ntitle = ii_htmlencode($strers);
  elseif(ii_isnull($ngenre)) $ntitle = ii_htmlencode($strers);//首页title
  else $ntitle = ii_htmlencode($strers) . SP_STR . $ntitle;
}

function mm_cnkeywords($strers)
{
  global $nkeywords;
  if (!ii_isnull($strers)) $nkeywords = ii_htmlencode($strers);
  else $nkeywords = $nkeywords;
}

function mm_cndescription($strers)
{
  global $ndescription;
  if (!ii_isnull($strers)) $ndescription = ii_htmlencode($strers);
  else $ndescription = $ndescription;
}

function mm_cnurl()
{
  global $nurl, $cnurl;
  if (ii_isnull($cnurl)) return $nurl;
  else return $cnurl;
}

function mm_ctype($types, $type = 0)
{
  if ($type == 0)
  {
    global $ctype;
    if (ii_isnull($ctype)) return $types;
    else return $ctype;
  }
  elseif ($type == 1)
  {
    global $cmtype;
    if (ii_isnull($cmtype)) return $types;
    else return $cmtype;
  }
}

function mm_client_alert($alert, $type)
{
  ob_end_clean();
  if (is_numeric($type)) $tdispose = 'history.go(' . $type . ')';
  else $tdispose = 'location.href=\'' . $type . '\'';
  $tstr = ii_ireplace('global.tpl_common.client_alert', 'tpl');
  $tstr = str_replace('{$alert}', $alert, $tstr);
  $tstr = str_replace('{$dispose}', $tdispose, $tstr);
  echo $tstr;
  exit();
}

function mm_client_redirect($url)
{
  ob_end_clean();
  $tstr = ii_ireplace('global.tpl_common.client_redirect', 'tpl');
  $tstr = str_replace('{$url}', $url, $tstr);
  echo $tstr;
  exit();
}

function mm_clear_show($msg, $type = 0)
{
  ob_end_clean();
  $thead = ii_ireplace('global.tpl_public.clear_head', 'tpl');
  if ($type == 1) $tbody = '<h1>' . SYS_NAME . '.' . $msg . '</h1>';
  else $tbody = $msg;
  $tfoot = ii_ireplace('global.tpl_public.clear_foot', 'tpl');
  $tstr = $thead . $tbody . $tfoot;
  echo $tstr;
  exit();
}

function mm_ck_valcode()
{
  $tbool = false;
  global $nvalidate;
  if ($nvalidate != 0)
  {
    if (strtolower($_POST['valcode']) == strtolower($_SESSION['valcode'])) $tbool = true;
    if (strtolower($_GET['valcode']) == strtolower($_SESSION['valcode'])) $tbool = true;
  }
  else $tbool = true;
  return $tbool;
}

function mm_check_valcode($strers)
{
  if (!mm_ck_valcode()) mm_client_alert(ii_itake('global.lng_error.valcode', 'lng'), $strers);
}

function mm_cvalhtml($template, $vals, $recurrence)
{
  $tmpstr = ii_ctemplate($template, $recurrence);
  if (ii_get_num($vals) == 0) $tmpstr = '';
  $tmpstr = str_replace(WDJA_CINFO, $tmpstr, $template);
  return $tmpstr;
}

function mm_cutepage_content($strers, $vars)
{
  if (ii_isnull($strers)) return;
  $tcp_note = ii_get_num(ii_get_strvalue($vars, 'cp_note'));
  if ($tcp_note == 0)
  {
    return $strers;
  }
  else
  {
    $tcp_mode = ii_get_num(ii_get_strvalue($vars, 'cp_mode'));
    $tcp_type = ii_get_num(ii_get_strvalue($vars, 'cp_type'));
    $tcp_num = ii_get_num(ii_get_strvalue($vars, 'cp_num'));
    $tcp_page = ii_get_num(ii_get_strvalue($vars, 'cp_page'));
    if ($tcp_page < 1) $tcp_page = 1;
    if ($tcp_mode == 0)
    {
      $tary = explode('[NextPage]', $strers);
      $tarycount = count($tary) - 1;
      $tcp_page -= 1;
      if ($tcp_page < 0) $tcp_page = 0;
      if ($tcp_page > $tarycount) $tcp_page = $tarycount;
      return $tary[$tcp_page];
    }
    else
    {
      if ($tcp_type == 0)
      {
        $tary = explode(CRLF, $strers);
        $tarycount = count($tary) - 1;
        $tcp_page = $tcp_page * $tcp_num;
        if ($tcp_page > $tarycount)
        {
          $tcp_page = $tarycount;
          $ti = floor($tcp_page/$tcp_num) * $tcp_num;
        }
        else
        {
          $ti = $tcp_page - $tcp_num;
          $tcp_page = $tcp_page - 1;
        }
        $tmpstr = '';
        for ($i = $ti; $i <= $tcp_page; $i ++)
        {
          $tmpstr .= $tary[$i] . CRLF;
        }
        return $tmpstr;
      }
      else
      {
        $tstrlen = strlen($strers);
        $tcp_page = $tcp_page * $tcp_num;
        if ($tcp_page > $tstrlen)
        {
          $tcp_page = $tstrlen;
          $ti = floor($tcp_page/$tcp_num) * $tcp_num;
          $tcp_page -= $ti;
        }
        else
        {
          $ti = $tcp_page - $tcp_num;
          $tcp_page = $tcp_num;
        }
        if ($ti < $tstrlen) return substr($strers, $ti, $tcp_page);
      }
    }
  }
}

function mm_cutepage_content_page($strers, $vars)
{
  $tcp_note = ii_get_num(ii_get_strvalue($vars, 'cp_note'));
  if ($tcp_note == 0)
  {
    return 0;
  }
  else
  {
    $tcp_mode = ii_get_num(ii_get_strvalue($vars, 'cp_mode'));
    $tcp_type = ii_get_num(ii_get_strvalue($vars, 'cp_type'));
    $tcp_num = ii_get_num(ii_get_strvalue($vars, 'cp_num'));
    if ($tcp_mode == 0)
    {
      $tary = explode('[NextPage]', $strers);
      return count($tary);
    }
    else
    {
      if ($tcp_num == 0) $tcp_num = 1000;
      if ($tcp_type == 0)
      {
        $tary = explode(CRLF, $strers);
        return ceil(count($tary) / $tcp_num);
      }
      else
      {
        $tstrlen = strlen($strers);
        return ceil($tstrlen / $tcp_num);
      }
    }
  }
}

function mm_cutepage_content_page_sel($strers, $vars, $ct_strers)
{
  global $nurltype;
  $tcp_note = ii_get_num(ii_get_strvalue($vars, 'cp_note'));
  $tcp_mode = ii_get_num(ii_get_strvalue($vars, 'cp_mode'));
  $tcp_type = ii_get_num(ii_get_strvalue($vars, 'cp_type'));
  $tcp_num = ii_get_num(ii_get_strvalue($vars, 'cp_num'));
  $tpagenum = mm_cutepage_content_page($strers, 'cp_note=' . $tcp_note . ';cp_mode=' . $tcp_mode . ';cp_type=' . $tcp_type . ';cp_num=' . $tcp_num);
  if ($tpagenum != 0)
  {
    $tpagelng = ii_itake('global.lng_cutepage.npage', 'lng');
    $ttpl_a_href_self = ii_itake('global.tpl_config.a_href_self', 'tpl');
    if ($tpagenum < 1) $tpagenum = 1;
    $tmpstr = '';
    for ($i = 1; $i <= $tpagenum; $i ++)
    {
      $tmpstr .= $ttpl_a_href_self;
      $tmpstr = str_replace('{$explain}', str_replace('[]', $i, $tpagelng), $tmpstr);
      $tmpstr = str_replace('{$value}', ii_iurl('cutepage', $i, $nurltype, $ct_strers), $tmpstr);
      if ($i != $tpagenum) $tmpstr .= ' ';
    }
    return $tmpstr;
  }
}

function mm_dbase_delete($table, $id, $idary, $osql = '')
{
  if (!(ii_isnull($table) || ii_isnull($id) || ii_isnull($idary)))
  {
    if (ii_cidary($idary))
    {
      global $conn;
      $tsqlstr = "delete from $table where $id in ($idary)";
      $tsqlstr .= $osql;
      return ii_conn_query($tsqlstr, $conn);
    }
  }
}

function mm_dbase_switch($table, $field, $id, $idary, $osql = '')
{
  if (!(ii_isnull($table) || ii_isnull($field) || ii_isnull($id) || ii_isnull($idary)))
  {
    if (ii_cidary($idary))
    {
      global $conn;
      $tsqlstr = "update $table set $field=abs($field-1) where $id in ($idary)";
      $tsqlstr .= $osql;
      return ii_conn_query($tsqlstr, $conn);
    }
  }
}

function mm_dbase_update($table, $field, $fieldValue, $id, $idary, $type = 0, $osql = '')
{
  if (!(ii_isnull($table) || ii_isnull($field) || ii_isnull($id) || ii_isnull($idary)))
  {
    if (ii_cidary($idary))
    {
      global $conn;
      if ($type == 0) $tsqlstr = "update $table set $field=$fieldValue where $id in ($idary)";
      else $tsqlstr = "update $table set $field='$fieldValue' where $id in ($idary)";
      $tsqlstr .= $osql;
      return ii_conn_query($tsqlstr, $conn);
    }
  }
}

function mm_echo_error()
{
  global $Err;
  $terrstr = '';
  if (is_array($Err))
  {
    foreach ($Err as $key => $val)
    {
      $terrstr .= $val . '\n';
    }
    $tmpstr = ii_itake('global.tpl_common.echo_error', 'tpl');
    $tmpstr = str_replace('{$message}', $terrstr, $tmpstr);
    return $tmpstr;
  }
}

function mm_exec_delete($table, $query)
{
  if (!ii_isnull($table))
  {
    global $conn;
    $tsqlstr = "delete from $table";
    if (!ii_isnull($query)) $tsqlstr .= $query;
    return ii_conn_query($tsqlstr, $conn);
  }
}

function mm_em_bar($strers)
{
  $tmpstr = ii_ireplace('global.tpl_common.em', 'tpl');
  $tmpstr = str_replace('{$content}', $strers, $tmpstr);
  return $tmpstr;
}

function mm_encode_content($content, $cttype, $ubbstate = 1)
{
  $tcttype = ii_get_num($cttype);
  $tubbstate = ii_get_num($ubbstate, 0);
  switch ($tcttype)
  {
    case 0:
      return ii_encode_text($content);
      break;
    case 1:
      return ii_encode_article(mm_ubbcode(ii_htmlencode($content), $tubbstate));
      break;
    case 2:
      return ii_encode_article(ii_htmlencode($content));
      break;
    case 3:
      return ii_encode_text($content);
      break;
  }
}

function mm_get_postarystr($value)
{
  $tvalue = $value;
  if (is_array($tvalue)) $tvalue = implode(',', $tvalue);
  else $tvalue = ii_cstr($tvalue);
  if (ii_isnull($tvalue)) $tvalue = '';
  return $tvalue;
}

function mm_get_mysortary($genre, $lng, $fsid)
{
  global $conn;
  global $sort_database, $sort_idfield, $sort_fpre;
  $tarys = Array();
  $tgenre = ii_get_safecode($genre);
  $tlng = ii_get_safecode($lng);
  $tfsid = ii_get_num($fsid);
  $tsqlstr = "select * from $sort_database where " . ii_cfnames($sort_fpre, 'fsid') . "=$tfsid and " . ii_cfnames($sort_fpre, 'genre') . "='$tgenre' and " . ii_cfnames($sort_fpre, 'lng') . "='$tlng' and " . ii_cfnames($sort_fpre, 'hidden') . "=0 order by " . ii_cfnames($sort_fpre, 'order') . " asc";
  $trs = ii_conn_query($tsqlstr, $conn);
  while ($trow = ii_conn_fetch_array($trs))
  {
    $tary[$trow[$sort_idfield]]['id'] = $trow[$sort_idfield];
    $tary[$trow[$sort_idfield]]['sort'] = $trow[ii_cfnames($sort_fpre, 'sort')];
    $tary[$trow[$sort_idfield]]['keywords'] = $trow[ii_cfnames($sort_fpre, 'keywords')];
    $tary[$trow[$sort_idfield]]['description'] = $trow[ii_cfnames($sort_fpre, 'description')];
    $tary[$trow[$sort_idfield]]['fid'] = $trow[ii_cfnames($sort_fpre, 'fid')];
    $tary[$trow[$sort_idfield]]['fsid'] = $trow[ii_cfnames($sort_fpre, 'fsid')];
    $tary[$trow[$sort_idfield]]['order'] = $trow[ii_cfnames($sort_fpre, 'order')];
    $tarys += $tary;
    $tarys += mm_get_mysortary($tgenre, $tlng, $trow[$sort_idfield]);
  }
  return $tarys;
}

function mm_get_sortary($genre, $lng)
{
  $tappstr = 'sys_sort_' . $genre . '_' . $lng;
  $tappstr = str_replace('/', '_', $tappstr);
  if (ii_cache_is($tappstr))
  {
    ii_cache_get($tappstr, 1);
  }
  else
  {
    $tary = mm_get_mysortary($genre, $lng, 0);
    ii_cache_put($tappstr, 1, $tary);
    $GLOBALS[$tappstr] = $tary;
  }
  return $GLOBALS[$tappstr];
}

function mm_get_sortids($genre, $lng)
{
  $tary = mm_get_sortary($genre, $lng);
  $tmpstr = '';
  foreach ($tary as $key => $val)
  {
    $tmpstr .= $key . ',';
  }
  if (ii_right($tmpstr, 1) == ',') $tmpstr = ii_left($tmpstr, strlen($tmpstr) - 1);
  return $tmpstr;
}

function mm_get_sortfid($fid, $id)
{
  if (ii_isnull($fid) || $fid == '0')
  {
    return $id;
  }
  else
  {
    return $fid . ',' . $id;
  }
}

function mm_get_sortfid_count($fid, $genre, $lng)
{
  global $conn;
  global $sort_database, $sort_idfield, $sort_fpre;
  $tsqlstr = "select count($sort_idfield) from $sort_database where " . ii_cfnames($sort_fpre, 'fid') . "='$fid' and " . ii_cfnames($sort_fpre, 'genre') . "='$genre' and " . ii_cfnames($sort_fpre, 'lng') . "='$lng'";
  $trs = ii_conn_query($tsqlstr, $conn);
  $trs = ii_conn_fetch_array($trs);
  return $trs[0];
}

function mm_get_sortfid_incount($fid)
{
  if ($fid == '0')
  {
    return -1;
  }
  else
  {
    return substr_count($fid, ',');
  }
}

function mm_get_sort_cls($id)
{
  global $conn;
  global $sort_database, $sort_idfield, $sort_fpre;
  $tid = ii_get_num($id);
  if (!($tid == 0))
  {
    $tsqlstr = "select * from $sort_database where $sort_idfield=$tid";
    $trs = ii_conn_query($tsqlstr, $conn);
    $trs = ii_conn_fetch_array($trs);
    if ($trs)
    {
      $tmpstr = '';
      $tfid = $trs[ii_cfnames($sort_fpre, 'fid')];
      $tfidary = explode(',', $tfid);
      foreach($tfidary as $key => $val)
      {
        $tmpstr .= '|' . $val . '|,';
      }
      $tmpstr .= '|' . $trs[$sort_idfield] . '|';
      return $tmpstr;
    }
  }
}

function mm_get_sorttext($genre, $lng, $id)
{
  $tary = mm_get_sortary($genre, $lng);
  if (is_array($tary))
  {
    foreach ($tary as $key => $val)
    {
      if ($key == $id) return $val['sort'];
    }
  }
}

function mm_get_sortkeywords($genre, $lng, $id)
{
  $tary = mm_get_sortary($genre, $lng);
  if (is_array($tary))
  {
    foreach ($tary as $key => $val)
    {
      if ($key == $id) return $val['keywords'];
    }
  }
}

function mm_get_sortdescription($genre, $lng, $id)
{
  $tary = mm_get_sortary($genre, $lng);
  if (is_array($tary))
  {
    foreach ($tary as $key => $val)
    {
      if ($key == $id) return $val['description'];
    }
  }
}

function mm_get_images_list($strers)
{
  if (!(ii_isnull($strers)))
  {
    $option_unselected = ii_itake('global.tpl_config.option_unselect', 'tpl');
    $tary = explode('|', $strers);
    $tmpstr = '';
    foreach ($tary as $key => $val)
    {
      $tstr = $option_unselected;
      $tstr = str_replace('{$explain}', $val, $tstr);
      $tstr = str_replace('{$value}', $val, $tstr);
      $tmpstr .= $tstr;
    }
    return $tmpstr;
  }
}

function mm_imessage($message, $backurl = '0')
{
  ob_end_clean();
  global $default_head, $default_foot;
  $tmyhead = mm_web_head($default_head);
  $tmyfoot = mm_web_foot($default_foot);
  if ($backurl == '0') $tmybody = ii_ireplace('global.tpl_common.web_messages', 'tpl');
  else $tmybody = ii_ireplace('global.tpl_common.web_message', 'tpl');
  $tmybody = str_replace('{$message}', $message, $tmybody);
  $tmybody = str_replace('{$backurl}', $backurl, $tmybody);
  $tmyhtml = $tmyhead . $tmybody . $tmyfoot;
  echo $tmyhtml;
  exit();
}

function mm_sendemail($address, $subject, $message)
{
  global $variable;
  $ttype = ii_get_num($variable['common.mail.type'], -1);
  $tsmtpcharset = $variable['common.mail.smtpcharset'];
  $tsmtpserver = $variable['common.mail.smtpserver'];
  $tsmtpport = $variable['common.mail.smtpport'];
  $tsmtpusername = $variable['common.mail.smtpusername'];
  $tsmtppassword = $variable['common.mail.smtppassword'];
  $tsmtpfromname = $variable['common.mail.smtpfromname'];
  $taddress = iconv(CHARSET, $tsmtpcharset, $address);
  $tsubject = iconv(CHARSET, $tsmtpcharset, $subject);
  $tmessage = iconv(CHARSET, $tsmtpcharset, $message);
  if ($ttype == -1) return;
  if ($ttype == 0)
  {
    return mail($taddress, $tsubject, $tmessage, "From: $tsmtpfromname");
  }
  elseif ($ttype == 1)
  {
    $tmail = new cc_socketmail;
    $tmail -> server = $tsmtpserver;
    $tmail -> port = $tsmtpport;
    $tmail -> username = $tsmtpusername;
    $tmail -> password = $tsmtppassword;
    $tmail -> from = $tsmtpfromname;
    $tmail -> to = $taddress;
    $tmail -> subject = $tsubject;
    $tmail -> message = $tmessage;
    return $tmail -> send_mail();
  }
  else
  {
    ini_set('SMTP', $tsmtpserver);
    ini_set('smtp_port', $tsmtpport);
    ini_set('sendmail_from', $tsmtpfromname);
    return mail($taddress, $tsubject, $tmessage, "From: $tsmtpfromname");
  }
}

function mm_sel_sort($genre, $lng, $sid)
{
  $tary = mm_get_sortary($genre, $lng);
  if (is_array($tary))
  {
    $tsid = ii_get_num($sid);
    $trestr = ii_itake('global.tpl_config.sys_spsort', 'tpl');
    $option_pre = '<option value="0" selected>'.ii_itake('global.lng_config.unselect', 'lng').'</option>';
    $option_unselected = ii_itake('global.tpl_config.option_unselect', 'tpl');
    $option_selected = ii_itake('global.tpl_config.option_select', 'tpl');
    $tmpstr = '';
    $treturnstr = '';
    foreach ($tary as $key => $val)
    {
      if ($key == $tsid) $tmpstr = $option_selected;
      else $tmpstr = $option_unselected;
      $tmpstr = str_replace('{$explain}', str_repeat($trestr, mm_get_sortfid_incount($val['fid'], ',') + 1) . $val['sort'], $tmpstr);
      $tmpstr = str_replace('{$value}', $val['id'], $tmpstr);
      $treturnstr .= $tmpstr;
    }
    return $option_pre.$treturnstr;
  }else{
    return $option_pre;
  }
}

function mm_sel_genre($genres, $genre)
{
  if (!ii_isnull($genres))
  {
    $option_unselected = ii_itake('global.tpl_config.option_unselect', 'tpl');
    $option_selected = ii_itake('global.tpl_config.option_select', 'tpl');
    $tmodules = ii_get_valid_module('string');
    $tmpstr = '';
    $treturnstr = '';
    $tary = explode(',', $genres);
    foreach ($tary as $key => $val)
    {
      if (ii_cinstr($tmodules, $val, '|'))
      {
        if ($val == $genre) $tmpstr = $option_selected;
        else $tmpstr = $option_unselected;
        $tmpstr = str_replace('{$explain}', ii_itake('global.' . $val . ':module.channel_title', 'lng'), $tmpstr);
        $tmpstr = str_replace('{$value}', $val, $tmpstr);
        $treturnstr .= $tmpstr;
      }
    }
    return $treturnstr;
  }
}

function mm_sel_yesno($name, $value)
{
  $option_radio = ii_itake('global.tpl_config.option_radio', 'tpl');
  $option_unradio = ii_itake('global.tpl_config.option_unradio', 'tpl');
  $tlngyes = ii_itake('global.lng_config.yes', 'lng');
  $tlngno = ii_itake('global.lng_config.no', 'lng');
  $tmpstr = '';
  $treturnstr = '';
  if ($value == 1) $tmpstr = $option_radio;
  else $tmpstr = $option_unradio;
  $tmpstr = str_replace('{$explain}', $name, $tmpstr);
  $tmpstr = str_replace('{$value}', 1, $tmpstr);
  $tmpstr = $tmpstr . $tlngyes . ' ';
  $treturnstr .= $tmpstr;
  if ($value == 0) $tmpstr = $option_radio;
  else $tmpstr = $option_unradio;
  $tmpstr = str_replace('{$explain}', $name, $tmpstr);
  $tmpstr = str_replace('{$value}', 0, $tmpstr);
  $tmpstr = $tmpstr . $tlngno . ' ';
  $treturnstr .= $tmpstr;
  return $treturnstr;
}

function mm_sel_control()
{
  global $ncontrol;
  if (!ii_isnull($ncontrol))
  {
    return  ii_show_xmlinfo_select('global.sel_control.all|' . $ncontrol, '', 'select');
  }
}

function mm_ubb_bar($strers)
{
  $tmpstr = ii_ireplace('global.tpl_common.ubb', 'tpl');
  $tmpstr = str_replace('{$content}', $strers, $tmpstr);
  return $tmpstr;
}

function mm_ubbcode($strers, $type = 0)
{
  global $global_images_route;
  $tstrers = $strers;
  if (!ii_isnull($tstrers))
  {
    $tstrers = preg_replace("/\[b\](.+?)\[\/b\]/is", "<b>\\1</b>", $tstrers);
    $tstrers = preg_replace("/\[u\](.+?)\[\/u\]/is", "<u>\\1</u>", $tstrers);
    $tstrers = preg_replace("/\[i\](.+?)\[\/i\]/is", "<i>\\1</i>", $tstrers);
    $tstrers = preg_replace("/\[h([1-6])\](.+?)\[\/h[1-6]\]/is", "<h\\1>\\2</h\\1>", $tstrers);
    $tstrers = preg_replace("/\[size=([1-5])\](.+?)\[\/size\]/is", "<font size=\"\\1\">\\2</font>", $tstrers);
    $tstrers = preg_replace("/\[color=(.[^\]]+)\](.+?)\[\/color\]/is", "<font color=\"\\1\">\\2</font>", $tstrers);
    $tstrers = preg_replace("/\[email\](.+)\[\/email\]/is", "<a href=\"mailto:\\1\">\\1</a>", $tstrers);
    $tstrers = preg_replace("/\[img\](.*javascript:.+?)\[\/img\]/is", "<!--img src=javascript-->", $tstrers);
    $tstrers = preg_replace("/\[img\](.+?)\[\/img\]/is", "<a href=\"\\1\" target=\"_blank\"><img src=\"\\1\" alt=\"\\1\" /></a>", $tstrers);
    $tstrers = preg_replace("/\[url\](http.+?)\[\/url\]/is", "<a href=\"\\1\" target=\"_blank\">\\1</a>", $tstrers);
    $tstrers = preg_replace("/\[url=(http.[^\]]+?)\](.+?)\[\/url\]/is", "<a href=\"\\1\" target=\"_blank\">\\2</a>", $tstrers);
    $tstrers = preg_replace("/\[align=(.[^\]]+)\](.+?)\[\/align\]/is", "<div align=\"\\1\" style=\"WIDTH:100%\">\\2</div>", $tstrers);
    $tstrers = preg_replace("/\[mp=*([0-9]*),*([0-9]*)\](.+?)\[\/mp\]/is", "<object align=\"middle\" classid=\"clsid:22d6f312-b0f6-11d0-94ab-0080c74c7e95\" class=\"object\" id=\"mediaplayer\" width=\"\\1\" height=\"\\2\" ><param name=\"showstatusbar\" value=\"-1\"><param name=\"filename\" value=\"\\3\"><embed type=\"application/x-oleobject\" codebase=\"http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#version=5,1,52,701\" flename=\"mp\" src=\"\\3\" width=\"\\1\" height=\"\\2\"></embed></object>", $tstrers);
    $tstrers = preg_replace("/\[em\](.+?)\[\/em\]/is", "<img src=\"" . $global_images_route . "em/\\1.gif\" alt=\"\\1\" />", $tstrers);
    if ($type == 1) $tstrers = preg_replace("/\[flash=*([0-9]*),*([0-9]*)\](.+?)\[\/flash\]/is", "<script type=\"text/javascript\">writeFlashHTML2(\"_version=8,0,0,0\" ,\"_swf=\\3\", \"_width=\\1\", \"_height=\\2\", \"_quality=high\");</script>", $tstrers);
    $tstrers = preg_replace("/\[code\](.+?)\[\/code\]/is","<table class=\"UBB_code\"><tr><td>\\1</td></tr></table>", $tstrers);
    $ti = 0; $tisquote = 1;
    while ($tisquote)
    {
      $tstrers = preg_replace("/\[quote\](.+?)\[\/quote\]/is","<table class=\"UBB_quote\"><tr><td>\\1</td></tr></table>", $tstrers);
      $ti += 1;
      if ($ti >= 5) $tisquote = 0;
      if (!is_numeric(strpos($tstrers, '[/quote]'))) $tisquote = 0;
    }
  }
  return $tstrers;
}

function mm_valcode()
{
  $valcode = ii_ireplace('global.tpl_common.valcode', 'tpl');
  return $valcode;
}

function mm_web_title($title)
{
  global $ngenre;
  $ttitle = $title;
  $tweb_topic = ii_itake('global.' . ADMIN_FOLDER . '/global:seo.topic', 'lng');//首页title
  $tweb_title = ii_itake('global.' . ADMIN_FOLDER . '/global:seo.title', 'lng');//网站title
  if (ii_isnull($ttitle)) $tweb_title = $tweb_topic;//未设置title,则显示首页title
  if (!(ii_isnull($ttitle))) $tweb_title = $ttitle . SP_STR . $tweb_title;
  if (!(ii_isnull($ttitle)) && ii_isnull($ngenre)) $tweb_title = $ttitle;//首页title
  if ((ii_isnull($ttitle)) && ii_isnull($ngenre)) $tweb_title = $tweb_topic;//首页title为空
  return $tweb_title;
}

function mm_web_keywords($keywords)
{
  $tkeywords = $keywords;
  $tweb_keywords = ii_itake('global.' . ADMIN_FOLDER . '/global:seo.keywords', 'lng');
  if (ii_isnull($tweb_keywords)) $tweb_keywords = ii_itake('global.module.web_keywords', 'lng');
  if (!(ii_isnull($tkeywords))) $tweb_keywords = $tkeywords;
  return $tweb_keywords;
}

function mm_web_description($description)
{
  $tdescription = $description;
  $tweb_description = ii_itake('global.' . ADMIN_FOLDER . '/global:seo.description', 'lng');
  if (ii_isnull($tweb_description)) $tweb_description = ii_itake('global.module.web_description', 'lng');
  if (!(ii_isnull($tdescription))) $tweb_description = $tdescription;
  return $tweb_description;
}

function mm_web_base()
{
  global $nbasehref, $variable;
  if (ii_isnull($nbasehref)) $nbasehref = $variable['common.nbasehref'];
  if ($nbasehref == 1) return ii_ireplace('global.tpl_public.base', 'tpl');
}

function mm_web_head($key)
{
  $thead = ii_ireplace('global.tpl_public.' . $key, 'tpl');
  return $thead;
}

function mm_web_foot($key)
{
  global $starttime;
  $tfoot = ii_ireplace('global.tpl_public.' . $key, 'tpl');
  $endtime = microtime(1);
  $protime = number_format((($endtime - $starttime) * 1000), 3, '.', '');
  $tfoot = $tfoot . CRLF . '<!--WDJA(1.0), Processed in ' . $protime . ' ms-->';
  //$tfoot = deny_mirrored_websites().$tfoot . CRLF . '<!--WDJA(1.0), Processed in ' . $protime . ' ms-->';
  return $tfoot;
}

function vv_itransfer($type, $tpl, $vars)
{
  global $conn, $variable, $ngenre;
  $tgenre = ii_get_strvalue($vars, 'genre');
  $ttopx = ii_get_num(ii_get_strvalue($vars, 'topx'));
  $tcls = ii_get_num(ii_get_strvalue($vars, 'cls'));
  $tclass = ii_get_num(ii_get_strvalue($vars, 'class'));
  $thtml = ii_get_num(ii_get_strvalue($vars, 'html'));
  $tbid = ii_get_num(ii_get_strvalue($vars, 'bid'));
  $tkeywords = ii_get_strvalue($vars, 'keywords');
  $tosql = ii_get_strvalue($vars, 'osql');
  $ttransVars = ii_get_strvalue($vars, 'transVars');
  //*****************************************************
  $tbsql = ii_get_strvalue($vars, 'bsql');
  $tdatabase = ii_get_strvalue($vars, 'database');
  $tidfield = ii_get_strvalue($vars, 'idfield');
  $tfpre = ii_get_strvalue($vars, 'fpre');
  //*****************************************************
  $tbaseurl = ii_get_strvalue($vars, 'baseurl');
  if ($ttopx >= 0)
  {
    if (ii_isnull($tbaseurl))
    {
      if (!ii_isnull($tgenre) && !($tgenre == $ngenre)) $tbaseurl = ii_get_actual_route($tgenre);
    }
    if (ii_isnull($tgenre)) $tgenre = $ngenre;
    $turltype = ii_get_num($variable[ii_cvgenre($tgenre) . '.nurltype']);
    $tcreatefolder = $variable[ii_cvgenre($tgenre) . '.ncreatefolder'];
    $tcreatefiletype = $variable[ii_cvgenre($tgenre) . '.ncreatefiletype'];
    if (ii_isnull($tbsql))
    {
      if (ii_isnull($tdatabase)) $tdatabase = $variable[ii_cvgenre($tgenre) . '.ndatabase'];
      if (ii_isnull($tidfield)) $tidfield = $variable[ii_cvgenre($tgenre) . '.nidfield'];
      if (ii_isnull($tfpre)) $tfpre = $variable[ii_cvgenre($tgenre) . '.nfpre'];
      if (!ii_isnull($tdatabase))
      {
        switch($type)
        {
          case 'all':
            $tsqlstr = "select * from $tdatabase where 1=1";
            $tsqlorder = " order by $tidfield desc";
            break;
          case 'search':
            $tsqlstr = "select * from $tdatabase where " . ii_cfnames($tfpre, 'topic') . " like '%" .$tkeywords. "%' or ". ii_cfnames($tfpre, 'content') . " like '%" .$tkeywords."%'";
            $tsqlorder = " order by $tidfield desc";
            break;
          case 'top':
            $tsqlstr = "select * from $tdatabase where " . ii_cfnames($tfpre, 'hidden') . "=0";
            $tsqlorder = " order by $tidfield desc";
            break;
          case 'hot':
            $tsqlstr = "select * from $tdatabase where " . ii_cfnames($tfpre, 'hidden') . "=0";
            $tsqlorder = " order by " . ii_cfnames($tfpre, 'count') . " desc";
            break;
          case 'new':
            $tsqlstr = "select * from $tdatabase where " . ii_cfnames($tfpre, 'hidden') . "=0";
            $tsqlorder = " order by " . ii_cfnames($tfpre, 'time') . " desc";
            break;
          case 'old':
            $tsqlstr = "select * from $tdatabase where " . ii_cfnames($tfpre, 'hidden') . "=0";
            $tsqlorder = " order by " . ii_cfnames($tfpre, 'time') . " asc";
            break;
          case 'good':
            $tsqlstr = "select * from $tdatabase where " . ii_cfnames($tfpre, 'hidden') . "=0 and " . ii_cfnames($tfpre, 'good') . "=1";
            $tsqlorder = " order by $tidfield desc";
            break;
          case 'up':
            $tsqlstr = "select * from $tdatabase where " . ii_cfnames($tfpre, 'hidden') . "=0 and $tidfield>$tbid";
            $tsqlorder = " order by $tidfield asc";
            $tips = ii_itake('global.lng_config.updata','lng') . '：';
            break;
          case 'down':
            $tsqlstr = "select * from $tdatabase where " . ii_cfnames($tfpre, 'hidden') . "=0 and $tidfield<$tbid";
            $tsqlorder = " order by $tidfield desc";
            $tips = ii_itake('global.lng_config.downdata','lng') . '：';
            break;
          default:
            $tsqlstr = "select * from $tdatabase where " . ii_cfnames($tfpre, 'hidden') . "=0";
            $tsqlorder = " order by $tidfield desc";
            break;
        }
        if ($tcls != 0) $tsqlstr .= " and " . ii_cfnames($tfpre, 'cls') . " like '%|" . $tcls . "|%'";
        if ($tclass != 0) $tsqlstr .= " and " . ii_cfnames($tfpre, 'class') . "=$tclass";
        if (!ii_isnull($tosql)) $tsqlstr .= $tosql;
        if (strpos($ttopx,',') !== false) $tsqlstr .= $tsqlorder . " limit $ttopx";
        else $tsqlstr .= $tsqlorder . " limit 0,$ttopx";
      }
      else $tsqlstr = $tbsql;
      $trs = ii_conn_query($tsqlstr, $conn);
      if (substr($tpl, 0, 7) == 'global.') $tmpstr = ii_itake($tpl, 'tpl');
      else $tmpstr = ii_itake('global.tpl_transfer.' . $tpl, 'tpl');
      if (!ii_isnull($tmpstr))
      {
        if (!ii_isnull($ttransVars))
        {
          $ttransVarsAry = explode('&', $ttransVars);
          foreach ($ttransVarsAry as $key => $val)
          {
            $ttransVarsArys = explode('=', $val);
            if (count($ttransVarsArys) == 2) $tmpstr = str_replace('{$' . $ttransVarsArys[0] . '}', $ttransVarsArys[1], $tmpstr);
          }
        }
        $tmpastr = ii_ctemplate($tmpstr, '{@}');
        while ($trow = ii_conn_fetch_array($trs))
        {
          $tmptstr = $tmpastr;
          foreach ($trow as $key => $val)
          {
            $tkey = ii_get_lrstr($key, '_', 'rightr');
            $tval = $val;
            $GLOBALS['RST_' . $tkey] = $tval;
            if ($thtml != 1) $tval = ii_htmlencode($tval);
            $tmptstr = str_replace('{$' . $tkey . '}', $tval, $tmptstr);
          }
          $tmptstr = str_replace('{$id}', $trow[$tidfield], $tmptstr);
          $tmptstr = str_replace('{$genre}', $tgenre, $tmptstr);
          $tmptstr = str_replace('{$baseurl}', $tbaseurl, $tmptstr);
          $tmptstr = str_replace('{$urltype}', $turltype, $tmptstr);
          $tmptstr = str_replace('{$createfolder}', $tcreatefolder, $tmptstr);
          $tmptstr = str_replace('{$createfiletype}', $tcreatefiletype, $tmptstr);
          $tmptstr = ii_creplace($tmptstr);
          $tmprstr .= $tmptstr;
        }
        $tmpstr = str_replace(WDJA_CINFO, $tmprstr, $tmpstr);
        if ((empty($tmprstr) && $type == 'up')||(empty($tmprstr) && $type == 'down')){
          $tipsnull = ii_itake('global.lng_config.nomore','lng');
        }elseif (empty($tmprstr)){
          //$tipsnull = ii_itake('global.lng_config.nodata','lng');
        }
        else $tipsnull ='';
        $tmpstr = $tips . ii_creplace($tmpstr).$tipsnull;
        return $tmpstr;
      }
      else return 'tpl.error';
    }
    else return 'genre.error';
  }
  else return 'topx.error';
}

function vv_inavigation($genre, $vars, $type='0')
{
  global $variable, $nlng;
  $tclassid = ii_get_num(ii_get_strvalue($vars, 'classid'));
  $tstrers = ii_get_strvalue($vars, 'strers');
  $tstrurl = ii_get_strvalue($vars, 'strurl');
  if($type==0){
    $tpl_href = ii_itake('global.tpl_config.a_href_self', 'tpl');
  }else{
    $tpl_href = ii_itake('global.tpl_config.span_href_self', 'tpl');
  }
  $tmpstr = ii_itake('global.module.channel_title', 'lng');
  $toutstr = $tpl_href;
  $toutstr = str_replace('{$explain}', $tmpstr, $toutstr);
  $toutstr = str_replace('{$value}', ii_get_actual_route('./'), $toutstr);
  if (!ii_isnull($tstrers))
  {
    $tmpstr = ii_itake('global.' . $genre . ':'. $tstrers .'.channel_title', 'lng');
    if (!ii_isnull($tstrurl))
    {
      $toutstr .= NAV_SP_STR . $tpl_href;
      $toutstr = str_replace('{$explain}', $tmpstr, $toutstr);
      $toutstr = str_replace('{$value}', ii_get_actual_route($tstrurl), $toutstr);
    }
    else $toutstr .= NAV_SP_STR . $tmpstr;
  }
  $tbaseurl = ii_get_actual_route($genre);
  if (ii_right($tbaseurl, 1) != '/') $tbaseurl .= '/';
  $turltype = ii_get_num($variable[ii_cvgenre($genre) . '.nurltype']);
  $tcreatefolder = $variable[ii_cvgenre($genre) . '.ncreatefolder'];
  $tcreatefiletype = $variable[ii_cvgenre($genre) . '.ncreatefiletype'];
  if ($tclassid != -1)
  {
    $tary = mm_get_sortary($genre, $nlng);
    if (is_array($tary))
    {
      foreach ($tary as $key => $val)
      {
        if ($key == $tclassid) $tfid = mm_get_sortfid($val['fid'], $val['id']);
      }
      if (isset($tfid))
      {
        foreach ($tary as $key => $val)
        {
          if (ii_cinstr($tfid, $key, ','))
          {
            $toutstr .= NAV_SP_STR . $tpl_href;
            $toutstr = str_replace('{$explain}', $val['sort'], $toutstr);
            $toutstr = str_replace('{$value}', ii_curl($tbaseurl, ii_iurl('list', $val['id'], $turltype, 'folder=' . $tcreatefolder . ';filetype=' . $tcreatefiletype)), $toutstr);
          }
        }
      }
    }
  }
  return $toutstr;
}

function vv_isort($genre, $vars, $sortAry = '')
{
  global $variable, $nlng;
  $tclassid = ii_get_num(ii_get_strvalue($vars, 'classid'));
  $ttpl = ii_get_strvalue($vars, 'tpl');
  $tgenre = ii_get_strvalue($vars, 'genre');
  if (!ii_isnull($tgenre) && $tgenre != $genre)
  {
    $tbaseurl = ii_get_actual_route($tgenre);
    if (ii_right($tbaseurl, 1) != '/') $tbaseurl .= '/';
  }
  if (ii_isnull($tgenre)) $tgenre = $genre;
  $turltype = ii_get_num($variable[ii_cvgenre($tgenre) . '.nurltype']);
  $tcreatefolder = $variable[ii_cvgenre($tgenre) . '.ncreatefolder'];
  $tcreatefiletype = $variable[ii_cvgenre($tgenre) . '.ncreatefiletype'];
  if (is_array($sortAry)) $tary = $sortAry;
  else $tary = mm_get_sortary($tgenre, $nlng);
  if (is_array($tary))
  {
    if (substr($ttpl, 0, 7) == 'global.') $tmpstr = ii_itake($ttpl, 'tpl');
    else $tmpstr = ii_itake('global.tpl_transfer.' . $ttpl, 'tpl');
    $tmpastr = ii_ctemplate($tmpstr, '{@}');
    $tmprstr = '';
    if (!ii_isnull($tmpstr))
    {
      foreach ($tary as $key => $val)
      {
        if ($val['fsid'] == $tclassid)
        {
          $tmptstr = str_replace('{$id}', $key, $tmpastr);
          $tmptstr = str_replace('{$sort}', $val['sort'], $tmptstr);
          $tmptstr = str_replace('{$baseurl}', $tbaseurl, $tmptstr);
          $tmptstr = str_replace('{$urltype}', $turltype, $tmptstr);
          $tmptstr = str_replace('{$createfolder}', $tcreatefolder, $tmptstr);
          $tmptstr = str_replace('{$createfiletype}', $tcreatefiletype, $tmptstr);
          $tmprstr .= $tmptstr;
        }
      }
      $tmpstr = str_replace(WDJA_CINFO, $tmprstr, $tmpstr);
      $tmpstr = ii_creplace($tmpstr);
      return $tmpstr;
    }
    else return 'tpl.error';
  }
}

function wdja_cms_setting()
{
  $tsite_skin = $_GET['site_skin'];
  $tsite_language = $_GET['site_language'];
  $tsite_template = $_GET['site_template'];
  if (!(ii_isnull($tsite_skin)))
  {
    setcookie(APP_NAME . 'config[skin]', $tsite_skin, time() + 31536000, COOKIES_PATH);
    $_COOKIE[APP_NAME . 'config']['skin'] = $tsite_skin;
  }
  if (!(ii_isnull($tsite_language)))
  {
    setcookie(APP_NAME . 'config[language]', $tsite_language, time() + 31536000, COOKIES_PATH);
    $_COOKIE[APP_NAME . 'config']['language'] = $tsite_language;
  }
  if (!(ii_isnull($tsite_template)))
  {
    setcookie(APP_NAME . 'config[template]', $tsite_template, time() + 31536000, COOKIES_PATH);
    $_COOKIE[APP_NAME . 'config']['template'] = $tsite_template;
  }
}

function wdja_cms_init($route)
{
  wdja_cms_setting();
  global $images_route, $global_images_route;
  global $nroute, $nlng, $nskin, $nuri, $nurs, $nurl, $nurlpre;
  $nroute = $route;
  $nlng = ii_get_active_things('lng');
  $nskin = ii_get_active_things('skin');
  $nuri = $_SERVER['SCRIPT_NAME'];
  $nurs = $_SERVER['QUERY_STRING'];
  $nport = $_SERVER['SERVER_PORT'];
  $nurl = $nuri;
  global $nckcode;
  $nckcode = ii_md5(ii_format_date(ii_now(), 2) . 'wdja');
  if (!(ii_isnull($nurs))) $nurl = $nuri . '?' . $nurs;
  if($nport == '443') $nurlpre = 'https://' . $_SERVER['HTTP_HOST'];
  else $nurlpre = 'http://' . $_SERVER['HTTP_HOST'];
  $images_route = ii_itake('global.tpl_config.images_route', 'tpl');
  $global_images_route = ii_get_actual_route($images_route);
  ii_conn_init();
  ii_get_variable_init();
  global $variable;
  global $sort_database, $sort_idfield, $sort_fpre;
  $sort_database = $variable['common.sort.ndatabase'];
  $sort_idfield = $variable['common.sort.nidfield'];
  $sort_fpre = $variable['common.sort.nfpre'];
  global $address_database, $address_idfield, $address_fpre;
  $address_database = $variable['passport.address.ndatabase'];
  $address_idfield = $variable['passport.address.nidfield'];
  $address_fpre = $variable['passport.address.nfpre'];
  $nvalidate = $variable['common.nvalidate'];
  mm_disable_ip();
}

function wdja_cms_web_head($key)
{
  return mm_web_head($key);
}

function wdja_cms_web_foot($key)
{
  return mm_web_foot($key);
}

function wdja_cms_web_noout()
{
  $tserver_v1 = $_SERVER['SERVER_NAME'];
  $tserver_v2 = $_SERVER["HTTP_REFERER"];
  $tlen = strlen($tserver_v1);
  $tckfrom = substr($tserver_v2, 7, $tlen);
  if($tckfrom != $tserver_v1) mm_imessage(ii_itake('global.lng_common.noout', 'lng'), -1);
}
//****************************************************
// WDJA CMS Power by wdja.cn
// Email: admin@wdja.cn
// Web: http://www.wdja.cn/
//****************************************************
?>