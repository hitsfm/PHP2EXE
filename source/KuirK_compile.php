<?php


ini_set('display_errors','off');

if(!$_ENV['TEMP']) $_ENV['TEMP']='c:/temp/';
if(!is_dir($_ENV['TEMP'])) mkdir($_ENV['TEMP']);

$help="
Kuirk Site2exe Compiler 1.0.2 (beta)
Thomas Favennec (2006)
http://www.redsofa.net

Syntax : KuirK_compile [-options] siteDirectory[,siteDir2...] destDirectory
(convert sites directories in one executable package in destDirectory)

- Options -

-my:mysqlData[,mysqlData2...] Mysql is added with specified datas,
-name:projectName Define the name of project,
-ext:extension.dll[,extension2.dll] Specified extensions is added
(extentions must be exists in kuirk extension directory),
-noc Files are not compiled like ApacheMagic Project
(package is fast but you can't use it on liveCD),
-browser:path Change default browser in package,
-fullscreen Full screen mode enabled (IE only).

"; //"

$compile=1;
$browser='';
$full=0;
$name;
$dest='';
$Asites='';
$Aext='';
$myliste='';
$mysql=0;

$k=0;
if(count($argv)>2)
{
 foreach($argv as $a)
 {
  if($i)
  {
   if($a{0}=='-')
   {
    $tab=explode(':',$a);
    $a=$tab[0];
    $a{0}='';
    $a=trim($a);
    for($j=1;$j<count($tab);$j++)
    {
     if($j>1) $b.=':';
     $b.=$tab[$j];
    }
    if($a=='my')
    {
     $myliste=explode(',',str_replace('\\','/',$b)); // '
     foreach($myliste as $m) if(!$m||!is_dir($m)) die("\n\n### KuirK error : $m not found ! ###\n");
    }
    else if($a=='name')
    {
     $name=$b;
     if(!trim($name)) die("\n\n### KuirK error : name needed ! ###\n");
    }
    else if($a=='ext')
    {
     $Aext=explode(',',str_replace('\\','/',$b)); // '
     foreach($Aext as $m) if(!$m||!is_file('extensions/'.$m)) die("\n\n### KuirK error : $m not found ! ###\n");
    }
    else if($a=='noc') $compile=0;
    else if($a=='browser')  $browser=$b;
    else if($a=='fullscreen') $full=1;
    else if($a=='h'||$a=='H') die($help);
    else die("\n\n### KuirK error : option unknowed ! ###\n");
   }
   else
   {
    $k++;
    if($k==1)
    {
     $Asites=explode(',',str_replace('\\','/',$a)); // '
     foreach($Asites as $m) if(!$m||!is_dir($m)) die("\n\n### KuirK error : $m not found ! ###\n");
    }
    if($k==2)
    {
     $dest=str_replace('\\','/',$a); //'
     if(!file_exists($dest)) mkdir($dest);
     if(!is_dir($dest)) die("\n\n### KuirK error : $dest is not directory ! ###\n");
     if(!$name)
     {
      $tab=explode('/',$dest);
      foreach($tab as $t) if(trim($t)) $name=$t;
     }
    }
    
   }


  }
  $i++;
 }

 if($myliste) $mysql=1;
 
 echo "\nPlease wait ...\n";
 KuirK_compile($compile,$name,$Asites,$dest,$Aext,$mysql,$myliste,$full,$browser);
 echo "done.\n";
 
}
else die($help);




/*
 compile un site web dans un package executable

 $compile 0|1
 $name nom du package
 $Asites tableau de path du/des site(s) internet
 $destination path de $destination du projet
 $Aextensions tableau des extensions a copier
 $mysql 0|1 ajoute ou non mysql
 $Amysql tableau des path des bases mysql
 $fullscreen 0|1 mode plein écran activé (pour IE)
 $browser path du navigateur utilisé

 exemple :  KuirK_compile(0,$name,$Asites,$destination,'',1,Array('C:/Program Files/EasyPHP1-8/mysql/data/redsofa'));

*/

function KuirK_compile($compile,$name,$Asites,$destination,$Aextensions='',$mysql=0,$Amysql='',$fullscreen=0,$browser='')
{
 if(!is_dir($destination)) mkdir($destination);
 if(!is_dir($_ENV['TEMP'])) mkdir($_ENV['TEMP']);
 if(!is_dir($_ENV['TEMP'].'/kuirk/')) mkdir($_ENV['TEMP'].'/kuirk/');
 if(!$fullscreen) $fullscreen='fullscreen = off'; else $fullscreen='fullscreen = on';
 if(!$browser) $browser='browser = c:/PROGRA~1/INTERN~1/IEXPLORE.EXE'; else $browser='browser = '.$browser;

 // copie le serveur
 $tab=ListRep('server');
 foreach($tab as $t)
 {
  $dirs=explode('/',dirname($_ENV['TEMP'].'/kuirk/'.$t));
  $test='';
  foreach($dirs as $d)
  {
   $test.=$d.'/';
   if(!is_dir($test)) mkdir($test);
  }
  copy('server/'.$t, $_ENV['TEMP'].'/kuirk/'.$t);
 }
 unlink($_ENV['TEMP'].'/kuirk/www/www');


 // copie mysql
 if($mysql)
 {
  $tab=ListRep('mysql');
  foreach($tab as $t)
  {
   $dirs=explode('/',dirname($_ENV['TEMP'].'/kuirk/mysql/'.$t));
   $test='';
   foreach($dirs as $d)
   {
    $test.=$d.'/';
    if(!is_dir($test)) mkdir($test);
   }
   copy('mysql/'.$t, $_ENV['TEMP'].'/kuirk/mysql/'.$t);
  }
 }


 // copie la/les bases mysql
 if($mysql&&$Amysql)
 {
  foreach($Amysql as $s)
  {
   $tab=explode('/',$s);
   $base=$tab[count($tab)-1];
   $tab=ListRep($s);
   foreach($tab as $t)
   {
    $dirs=explode('/',dirname($_ENV['TEMP'].'/kuirk/mysql/data/'.$base.'/'.$t));
    $test='';
    foreach($dirs as $d)
    {
     $test.=$d.'/';
     if(!is_dir($test)) mkdir($test);
    }
    copy($s.'/'.$t, $_ENV['TEMP'].'/kuirk/mysql/data/'.$base.'/'.$t);
   }
  }
 }


 // copie les extensions
 if($Aextensions) foreach($Aextensions as $t)
 {
  $fp=fopen($_ENV['TEMP'].'/kuirk/defaultconfig/php.ini','a+');
  if(copy('extensions/'.$t,$_ENV['TEMP'].'/kuirk/php/extensions/'.$t)) fwrite($fp,"\r\n".'extension='.$t);
  fclose($fp);
 }
 

 // copies le/les sites Web
 if($Asites) foreach($Asites as $s)
 {
   if(count($Asites)>1)
   {
    $dirs=explode('/',$s);
    foreach($dirs as $d) if(trim($d)) $site=$d;
    if(!file_exists($_ENV['TEMP'].'/kuirk/www/'.$site)) mkdir($_ENV['TEMP'].'/kuirk/www/'.$site);
   }
   else $site='';
   $tab=ListRep($s);
   foreach($tab as $t)
   {
    $myfile=$_ENV['TEMP'].'/kuirk/www/'.$site.'/'.$t;
    $dirs=explode('/',dirname($myfile));
    $test='';
    foreach($dirs as $d)
    {
     $test.=$d.'/';
     if(!is_dir($test)) mkdir($test);
    }
    copy($s.'/'.$t,$myfile);
   }
   

 }

 
 // compile le projet
 if($compile)
 {
  // compile en kui
  $dirs=explode('/',dirname($destination));
  $test='';
  foreach($dirs as $d)
  {
   $test.=$d.'/';
   if(!is_dir($test)) mkdir($test);
  }
  if(!is_dir($destination.'/'.$name)) mkdir($destination.'/'.$name);
  zasBlendDirectory('',$destination.'/'.$name.'/'.$name.'.kui',$_ENV['TEMP'].'/kuirk/');

  // ajoute launcher.exe
  copy('bin/launcher_c.exe',$destination.'/'.$name.'/'.$name.'.exe');

 }
 else
 {
   // ne compile pas
   $tab=ListRep($_ENV['TEMP'].'/kuirk/');
   foreach($tab as $t)
   {
    $dirs=explode('/',dirname($destination.'/'.$name.'/'.$t));
    $test='';
    foreach($dirs as $d)
    {
     $test.=$d.'/';
     if(!is_dir($test)) mkdir($test);
    }
    copy($_ENV['TEMP'].'/kuirk/'.$t, $destination.'/'.$name.'/'.$t);
   }

  // ajoute launcher.exe
  copy('bin/launcher.exe',$destination.'/'.$name.'/'.$name.'.exe');
 }
 
 
 // ajoute kuirk.ini
 $fp=fopen($destination.'/'.$name.'/Kuirk.ini','w+');
 fwrite($fp,$browser."\r\n".$fullscreen."\r\n");
 fclose($fp);

 
 // efface le temp
 remove_directory($_ENV['TEMP'].'/kuirk/');

 
}







function zasBlendDirectory($rep,$filename,$dir='')
{
 $r=1;
 if(is_dir($dir.'/'.$rep))
 {
  $tab=array_unique(ListRep($dir,$rep));
  $r=zasBlendFiles($tab,$filename,$dir);
 }
 else $r=0;
 return($r);
}


function zasBlendFiles($fileList,$filename,$dir0)
{
 $r=1;
 if(!is_dir($_ENV['TEMP'])) mkdir('temp/') or $r=0;

 if(!is_array($fileList)) $fileList=array($fileList);
 $zp=fopen($_ENV['TEMP'].'/_0.tmp','w+');
 foreach($fileList as $f)
 {
  $fi=basename($f);
  $dir=dirname($f);
  if(is_file($dir0.'/'.$dir.'/'.$fi))
  {
   $size=filesize($dir0.'/'.$dir.'/'.$fi);
   if($size>0)
   {
    $fp=fopen($dir0.'/'.$dir.'/'.$fi,'r');
    fwrite($zp,fread($fp,$size));
    fclose($fp);
    $corr[]=array($dir.'/'.$fi,$size);
   }
   $i++;
  }
  else
  {
   echo 'ZAS ERROR : ['.$dir.'/'.$fi.'] in fileList not found !'."\n";
   $r=0;
  }
 }
 fclose($zp);

 if($corr)
 {
  $fp=fopen($_ENV['TEMP'].'/temp_z.tmp','w+b');
  fwrite($fp,'          ');
  foreach($corr as $f)
  {
   list($a,$b)=$f;
   fwrite($fp,$a."\t".$b."\n");
  }
  $t=ftell($fp);
  $fp2=fopen($_ENV['TEMP'].'/_0.tmp','r');
  while(!feof($fp2)) fwrite($fp,fread($fp2,10000));
  fclose($fp2);
  unlink($_ENV['TEMP'].'/_0.tmp');

  fseek($fp,0,SEEK_SET);
  fwrite($fp,$t);
  fclose($fp);

  //final Z file
  $fp=fopen($_ENV['TEMP'].'/temp_z.tmp','rb');
  $fz=gzopen($filename,'wb9');
  gzwrite($fz,fread($fp,filesize($_ENV['TEMP'].'/temp_z.tmp')));
  gzclose($fz);
  fclose($fp);
  unlink($_ENV['TEMP'].'/temp_z.tmp');

 }
 else
 {
  echo 'ZAS ERROR : tmp files not found !'."\n";
  $r=0;
 }
 return($r);
}




function ListRep($dir='',$rep='')
{
 $tab=array();
 if (file_exists($dir.'/'.$rep))
 {
  $dh = opendir($dir.'/'.$rep);
  while (($file = readdir($dh))!=false) if($file!='..'&&$file!='.')
  {
   if(is_dir($dir.'/'.$rep.'/'.$file))
   {
    $tab2=ListRep($dir,$rep.'/'.$file);
    foreach($tab2 as $t) $tab[]=$t;
   }
   else $tab[]=$rep.'/'.$file;
  }
  closedir($dh);
 }
 return $tab;
}



function remove_directory($dir)
{
 if ($handle = opendir($dir))
 {
  while(false!==($item=readdir($handle)))
  {
   if ($item!='.'&&$item!='..')
   {
    if (is_dir($dir.'/'.$item)) remove_directory($dir.'/'.$item);
    else unlink($dir.'/'.$item);
   }
  }
  closedir($handle);
  rmdir($dir);
 }
}


?>
