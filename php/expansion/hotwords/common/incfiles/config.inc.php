<?php
  $nroute = 'child';
  $ngenre = ii_get_actual_genre(__FILE__, $nroute);
wdja_cms_init($nroute);
$nhead = $variable[ii_cvgenre($ngenre) . '.nhead'];
$nfoot = $variable[ii_cvgenre($ngenre) . '.nfoot'];
if (ii_isnull($nhead)) $nhead = $default_head;
if (ii_isnull($nfoot)) $nfoot = $default_foot;
$nhotwords_genre = $variable[ii_cvgenre($ngenre) . '.nhotwords_genre'];
$ndatabase = $variable[ii_cvgenre($ngenre) . '.ndatabase'];
$nidfield = $variable[ii_cvgenre($ngenre) . '.nidfield'];
$nfpre = $variable[ii_cvgenre($ngenre) . '.nfpre'];
$npagesize = $variable[ii_cvgenre($ngenre) . '.npagesize'];
$nlisttopx = $variable[ii_cvgenre($ngenre) . '.nlisttopx'];
$nurltype = $variable[ii_cvgenre($ngenre) . '.nurltype'];
$nclstype = $variable[ii_cvgenre($ngenre) . '.nclstype'];
$nbasehref = $variable[ii_cvgenre($ngenre) . '.nbasehref'];
$ntitle = ii_itake('module.channel_title', 'lng');
$nkeywords = $variable[ii_cvgenre($ngenre) . '.nkeywords'];
$ndescription = $variable[ii_cvgenre($ngenre) . '.ndescription'];
?>
