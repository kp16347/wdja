<?php
//****************************************************
// WDJA CMS Power by wdja.cn
// Email: admin@wdja.cn
// Web: http://www.wdja.cn/
//****************************************************
function wdja_cms_admin_manage_delete()
{
  global $nuri;
  $tfile = $_GET['file'];
  $tcache_dir = ii_get_actual_route('./') . CACHE_DIR;
  $tfilename = $tcache_dir . '/' . $tfile;
  $tfilename = iconv (CHARSET, 'cp936', $tfilename);
  if (unlink($tfilename))
  {
    header('location: ' . $nuri);
  }
  else
  {
    wdja_cms_admin_msg(ii_itake('manage.delete_error', 'lng'), $nuri, 1);
  }
}

function wdja_cms_admin_manage_removeall()
{
  global $nuri;
  @ii_cache_remove();
  header('location: ' . $nuri);
}

function wdja_cms_admin_manage_action()
{
  switch($_GET['action'])
  {
    case 'delete':
      wdja_cms_admin_manage_delete();
      break;
    case 'removeall':
      wdja_cms_admin_manage_removeall();
      break;
  }
}

function wdja_cms_admin_manage_list()
{
  $tmpstr = ii_ireplace('manage.list', 'tpl');
  $tmpastr = ii_ctemplate($tmpstr, '{#recurrence_ida}');
  $tcache_dir = ii_get_actual_route('./') . CACHE_DIR;
  $tcdirs = dir($tcache_dir);
  while($tentry = $tcdirs -> read())
  {
    if (is_numeric(strpos($tentry, '.')))
    {
      $ttentry = iconv('cp936', CHARSET, $tentry);
      $tcachename = ii_get_lrstr($ttentry, '.', 'left');
      if (!(ii_isnull($tcachename)))
      {
        $tmptstr = str_replace('{$cache_name}', $tcachename, $tmpastr);
        $tmptstr = str_replace('{$file_name}', urlencode($ttentry), $tmptstr);
        $tmprstr = $tmprstr . $tmptstr;
      }
    }
  }
  $tcdirs -> close();
  $tmpstr = str_replace(WDJA_CINFO, $tmprstr, $tmpstr);
  return $tmpstr;
}

function wdja_cms_admin_manage()
{
  switch($_GET['type'])
  {
    case 'list':
      return wdja_cms_admin_manage_list();
      break;
    default:
      return wdja_cms_admin_manage_list();
      break;
  }
}
//****************************************************
// WDJA CMS Power by wdja.cn
// Email: admin@wdja.cn
// Web: http://www.wdja.cn/
//****************************************************
?>
