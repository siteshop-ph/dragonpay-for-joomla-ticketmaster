<?php




// Only php error will be show (NOT warning, NOT Notice, etc)
error_reporting(E_ERROR);








call_user_func(function() {


//clearstatcache() ;
	 

require_once (dirname(__FILE__).'/config.php'); // to get merchandid & api password & CMS path











                     if ($debug_mode == 1) {


    date_default_timezone_set('Asia/Manila');
    $date = new DateTime();
    // echo $date->format('Y-m-d\TH:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
    // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise



                      // log.txt 
                      // no confidential data here, so .txt is ok
                         $fp = fopen(dirname(__FILE__).'/log.txt', 'a');  
                   fwrite($fp, $date->format('Y-m-d\TH:i:s').":  START CRON"."\n"); 
                                 fclose($fp);  
                     }













// Create the file that will serve to send the request to Dragonpay
 //file content, taken from here: http://test.dragonpay.ph/DragonpayWebService/MerchantService.asmx?op=GetMerchantTxns 
   //  SOAP 1.2 version & starting from first line with <?xml  and where for escaping this character "  ,    " is remplaced by \"  
   // we preffer re-create this file each time, in case someone as server admin modify it by ignorance
$content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<soap12:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap12=\"http://www.w3.org/2003/05/soap-envelope\">
  <soap12:Body>
    <GetMerchantTxns xmlns=\"http://api.dragonpay.ph/\">
      <merchantId>string</merchantId>
      <password>string</password>
      <dStart>dateTime</dStart>
      <dEnd>dateTime</dEnd>
    </GetMerchantTxns>
  </soap12:Body>
</soap12:Envelope>";
 
 // Create the file with the content for send the request to Dragonpay, for better security (as later there will be password within this file), we prefer to save on .php file vs .xml file as .xml could be opened or downloaded if there are located in the public_html directory or in a public_html sub directory
$fp = fopen(dirname(__FILE__).'/xml_request_GetMerchantTxns.php','wb');  
fwrite($fp,$content);
fclose($fp);










// Replace the xml node for password in the xml file to post to Dragonpay Web Service
    
    // read the file
    $file_content = file_get_contents(dirname(__FILE__).'/xml_request_GetMerchantTxns.php');

    $oldNodePassword = '#(<password.*?>).*?(</password>)#';
    $newNodePassword = "<password>$ws_password</password>";

    // replace the data      
    $file_content = preg_replace($oldNodePassword, $newNodePassword, $file_content);
   
    // write the file
    file_put_contents(dirname(__FILE__).'/xml_request_GetMerchantTxns.php', $file_content);







// Replace the xml node for merchantId in the xml file to post to Dragonpay Web Service

    // read the file
    $file_content = file_get_contents(dirname(__FILE__).'/xml_request_GetMerchantTxns.php');

    $oldNodemerchantId = '#(<merchantId.*?>).*?(</merchantId>)#';
    $newNodemerchantId = "<merchantId>$ws_merchantId</merchantId>";

    // replace the data      
    $file_content = preg_replace($oldNodemerchantId, $newNodemerchantId, $file_content);
   
    // write the file
    file_put_contents(dirname(__FILE__).'/xml_request_GetMerchantTxns.php', $file_content);


















// Create DateEnd variable
function DateEnd() {
    date_default_timezone_set('Asia/Manila');
    $date = new DateTime();
    echo $date->format('Y-m-d\TH:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
   // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise
}
ob_start();
DateEnd();
$DateEnd = ob_get_clean();

// For test purpose
//echo $DateEnd;  // to check that the DateTime match to Manila Time








// Create DateStart variable
function DateStart() {
    date_default_timezone_set('Asia/Manila');
    $date = new DateTime();
    $date->modify('-7 day');           // to get DateStart set at 7 days from now, this for check status of transactions from OTC
    echo $date->format('Y-m-d\TH:i:s'); // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
}
ob_start();
DateStart();
$DateStart = ob_get_clean();

// For test purpose
//echo $DateStart;  // to check that the DateTime match to Manila Time

















// Replace the xml node for dEnd (DateEnd) in the xml file to post to Dragonpay Web Service

    // read the file
    $file_content = file_get_contents(dirname(__FILE__).'/xml_request_GetMerchantTxns.php');

    $oldNodeDateEnd = '#(<dEnd.*?>).*?(</dEnd>)#';
    $newNodeDateEnd = "<dEnd>$DateEnd</dEnd>";

    // replace the data      
    $file_content = preg_replace($oldNodeDateEnd, $newNodeDateEnd, $file_content);
   
    // write the file
    file_put_contents(dirname(__FILE__).'/xml_request_GetMerchantTxns.php', $file_content);







// Replace the xml node for dStart (DateStart) in the xml file to post to Dragonpay Web Service

    // read the file
    $file_content = file_get_contents(dirname(__FILE__).'/xml_request_GetMerchantTxns.php');

    $oldNodeDateStart = '#(<dStart.*?>).*?(</dStart>)#';
    $newNodeDateStart = "<dStart>$DateStart</dStart>";

    // replace the data      
    $file_content = preg_replace($oldNodeDateStart, $newNodeDateStart, $file_content);
   
    // write the file
    file_put_contents(dirname(__FILE__).'/xml_request_GetMerchantTxns.php', $file_content);








 echo PHP_EOL . 'Prepare request to Dragonpay: last 7 days transactions' . PHP_EOL . PHP_EOL . PHP_EOL;








                     if ($debug_mode == 1) {


    date_default_timezone_set('Asia/Manila');
    $date = new DateTime();
    // echo $date->format('Y-m-d\TH:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
    // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise



                      // log.txt 
                      // no confidential data here, so .txt is ok
                         $fp = fopen(dirname(__FILE__).'/log.txt', 'a');                                        
                  fwrite($fp, $date->format('Y-m-d\TH:i:s').":  Prepare request to Dragonpay: last 7 days transactions"."\n");                                      
                                 fclose($fp);  
                      }











 $file_xml_request_GetMerchantTxns = dirname(__FILE__).'/xml_request_GetMerchantTxns.php';
 $file_xml_answer_GetMerchantTxns = dirname(__FILE__).'/xml_answer_GetMerchantTxns.php';









// Send the request to Dragonpay
  // xml request file content is same as here: http://test.dragonpay.ph/DragonpayWebService/MerchantService.asmx?op=GetMerchantTxns 
 //  SOAP 1.2 version & starting from first line with <?xml




// DRAGONPAY TEST ACCOUNT CASE
if ($test_mode == 1){
	 ## We're in a testing environment.





                                           echo "Dragonpay TEST ACCOUNT used" . PHP_EOL . PHP_EOL;



                                       if ($debug_mode == 1) {

                                          date_default_timezone_set('Asia/Manila');
                                          $date = new DateTime();
                                          // echo $date->format('Y-m-d\TH:i:s'); 
                                           // echo $date->format('Y-m-d');  

                                           // log.txt 
                                           // no confidential data here, so .txt is ok
                                          $fp = fopen(dirname(__FILE__).'/log.txt', 'a');                                        
                                       fwrite($fp, $date->format('Y-m-d\TH:i:s').":  Dragonpay TEST ACCOUNT used" ."\n");                                      
                                       fclose($fp);  
                                      }



             


         exec('wget https://test.dragonpay.ph/DragonpayWebService/MerchantService.asmx --post-file='.$file_xml_request_GetMerchantTxns.' --header="Content-Type: application/soap+xml; charset=utf-8" -O '.$file_xml_answer_GetMerchantTxns.'');

         // can work also
  //exec('wget --post-file "xml_request_GetMerchantTxns.php" --header "Content-Type: application/soap+xml; charset=utf-8" http://test.dragonpay.ph/DragonpayWebService/MerchantService.asmx -O xml_answer_GetMerchantTxns.php');  



                         // clearstatcache();


                         // get contents of a file into a string
                         $filename_path = dirname(__FILE__) . '/xml_answer_GetMerchantTxns.php';


                         echo "Answer Size: ". filesize($filename_path) . PHP_EOL . PHP_EOL;





                                       if ($debug_mode == 1) {

                                          date_default_timezone_set('Asia/Manila');
                                          $date = new DateTime();
                                          // echo $date->format('Y-m-d\TH:i:s'); 
                                           // echo $date->format('Y-m-d');  

                                           // log.txt 
                                           // no confidential data here, so .txt is ok
                                          $fp = fopen(dirname(__FILE__).'/log.txt', 'a');                                        
                                       fwrite($fp, $date->format('Y-m-d\TH:i:s').":  Answer Size: " . filesize($filename_path) ."\n");                                      
                                       fclose($fp);  
                                      }



          

         // if file is like empty answer          
         if (filesize($filename_path) < 314) { 



               echo "ERROR establishing connection to Dragonpay" . PHP_EOL . PHP_EOL;

               echo "EXITING CRON" . PHP_EOL . PHP_EOL;






                     if ($debug_mode == 1) {

    date_default_timezone_set('Asia/Manila');
    $date = new DateTime();
    // echo $date->format('Y-m-d\TH:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
    // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise

                      // log.txt 
                      // no confidential data here, so .txt is ok
                         $fp = fopen(dirname(__FILE__).'/log.txt', 'a');                                        
                  fwrite($fp, $date->format('Y-m-d\TH:i:s').":  ERROR establishing connection to Dragonpay"."\n");  
                  fwrite($fp, $date->format('Y-m-d\TH:i:s').":  EXITING CRON"."\n");                                     
                                 fclose($fp);  
                      }


                   exit();   // quitt the script

               }











                       
        // LIVE DRAGONPAY ACCOUNT CASE
	}else{








                         echo "Dragonpay LIVE ACCOUNT used" . PHP_EOL . PHP_EOL;



                                       if ($debug_mode == 1) {

                                          date_default_timezone_set('Asia/Manila');
                                          $date = new DateTime();
                                          // echo $date->format('Y-m-d\TH:i:s'); 
                                           // echo $date->format('Y-m-d');  

                                           // log.txt 
                                           // no confidential data here, so .txt is ok
                                          $fp = fopen(dirname(__FILE__).'/log.txt', 'a');                                        
                                       fwrite($fp, $date->format('Y-m-d\TH:i:s').":  Dragonpay LIVE ACCOUNT used" ."\n");                                      
                                       fclose($fp);  
                                      }









		## Use the lines below for a live site.
		 exec('wget https://secure.dragonpay.ph/DragonPayWebService/MerchantService.asmx --post-file='.$file_xml_request_GetMerchantTxns.' --header="Content-Type: application/soap+xml; charset=utf-8" -O '.$file_xml_answer_GetMerchantTxns.'');



                         // clearstatcache();


                         // get contents of a file into a string
                         $filename_path = dirname(__FILE__) . '/xml_answer_GetMerchantTxns.php';


                         echo "Answer Size: ". filesize($filename_path) . PHP_EOL . PHP_EOL;






                                       if ($debug_mode == 1) {

                                          date_default_timezone_set('Asia/Manila');
                                          $date = new DateTime();
                                          // echo $date->format('Y-m-d\TH:i:s'); 
                                           // echo $date->format('Y-m-d');  

                                           // log.txt 
                                           // no confidential data here, so .txt is ok
                                          $fp = fopen(dirname(__FILE__).'/log.txt', 'a');                                        
                                       fwrite($fp, $date->format('Y-m-d\TH:i:s').":  Answer Size: " . filesize($filename_path) ."\n");                                      
                                       fclose($fp);  
                                      }








         // if file is like empty answer 
         if (filesize($filename_path) < 314) { 



                echo "ERROR establishing connection to Dragonpay" . PHP_EOL . PHP_EOL;

  
                echo "EXITING CRON" . PHP_EOL . PHP_EOL;







                     if ($debug_mode == 1) {

    date_default_timezone_set('Asia/Manila');
    $date = new DateTime();
    // echo $date->format('Y-m-d\TH:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
    // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise

                      // log.txt 
                      // no confidential data here, so .txt is ok
                         $fp = fopen(dirname(__FILE__).'/log.txt', 'a');                                        
                  fwrite($fp, $date->format('Y-m-d\TH:i:s').":  ERROR establishing connection to Dragonpay"."\n");   
                  fwrite($fp, $date->format('Y-m-d\TH:i:s').":  EXITING CRON"."\n");                                      
                                 fclose($fp);  
                     } 


                     exit();   // quitt the script

              }


               
   	}












 


// For test purpose display "echo" all downloaded data from dragonpay
//echo file_get_contents(dirname(__FILE__).'/xml_answer_GetMerchantTxns.php');










 echo PHP_EOL . 'Got transactions data from Dragonpay' . PHP_EOL . PHP_EOL;









                     if ($debug_mode == 1) {


    date_default_timezone_set('Asia/Manila');
    $date = new DateTime();
    // echo $date->format('Y-m-d\TH:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
    // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise



                      // log.txt 
                      // no confidential data here, so .txt is ok
                         $fp = fopen(dirname(__FILE__).'/log.txt', 'a');                                        
                  fwrite($fp, $date->format('Y-m-d\TH:i:s').":  Got transactions data from Dragonpay"."\n");                                      
                                 fclose($fp);  
                      }












// For additional security ? , delete the file
unlink(dirname(__FILE__).'/xml_request_GetMerchantTxns.php');









// Deal the Dragonpay answer

    // Remove all ";" character, as it will be used as csv separator later, and could maybe be present in the "description" field 
            // 's/text_to_find/remplace_with_this/g'   and with escaping with \ both ' if not that will block below command
    exec('sed -i \'s/;/ /g\' '.$file_xml_answer_GetMerchantTxns.'');
    // can work also but "perl"  in maybe less present some OS
   // exec('perl -i -pe \'s/;/ /g\' "xml_answer_GetMerchantTxns.xml"');






// convert xml to csv for usability of data

   // for XmlToCsv convertion
   require_once(dirname(__FILE__).'/class2.php'); 

   $x = new XmlToCsv();

   // in the XML answer from Dragonpay, the transactions are actually on the 5th level
   $x->item('/*/*/*/*/*'); 

   $x->delimiter = ";";

   // xml to csv convertion
   $csvString = $x->url(dirname(__FILE__).'/xml_answer_GetMerchantTxns.php')->autoConvert();

   // Create the file with the content answer from Dragonpay, for better security, we prefer to save on .php file vs .xml file as .xml could be opened or downloaded if there are located in the public_html directory or in a public_html sub directory
   file_put_contents(dirname(__FILE__).'/csv_answer_GetMerchantTxns.php',$csvString);








// For additional security ? , delete the file
unlink(dirname(__FILE__).'/xml_answer_GetMerchantTxns.php');














// Update db & create ticket if needed 
      require_once (dirname(__FILE__).'/synchronization.php');   

    










// For additional security ? , delete the file
unlink(dirname(__FILE__).'/csv_answer_GetMerchantTxns.php');





 echo PHP_EOL . 'Synchronization DONE' . PHP_EOL . PHP_EOL . PHP_EOL;


 echo PHP_EOL . 'END CRON' . PHP_EOL . PHP_EOL . PHP_EOL;







                     if ($debug_mode == 1) {


    date_default_timezone_set('Asia/Manila');
    $date = new DateTime();
    // $date->format('Y-m-d\TH:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
   // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise



                      // log.txt 
                      // no confidential data here, so .txt is ok
                         $fp = fopen(dirname(__FILE__).'/log.txt', 'a');
                             fwrite($fp, $date->format('Y-m-d\TH:i:s').":  Synchronization DONE"."\n");                                       
                             fwrite($fp, $date->format('Y-m-d\TH:i:s').":  END CRON"."\n");                                      
                                 fclose($fp);  
                        }










 }); // END   call_user_func





?>
