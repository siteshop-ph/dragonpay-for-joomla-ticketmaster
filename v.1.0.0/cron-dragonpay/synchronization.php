<?PHP





// IMPORTANT:    this file is using  joomla database framework connexion    VS   using mysqli




         //init Joomla Framework 
         define( '_JEXEC', 1 ); 

         require_once (dirname(__FILE__).'/config.php');   // to get $CMS_path & config parameters

         
         define( 'JPATH_BASE', $CMS_path ); 
         define( 'DS', DIRECTORY_SEPARATOR ); 
  
         require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' ); 
         require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' ); 
         require_once ( JPATH_CONFIGURATION   .DS.'configuration.php' ); 
         require_once ( JPATH_LIBRARIES .DS.'joomla'.DS.'database'.DS.'database.php' ); 
         require_once ( JPATH_LIBRARIES .DS.'import.php' ); 









        // Load language  Ref.:  http://docs.joomla.org/Specification_of_language_files    
          $language = JFactory::getLanguage();
          $language->load('com_ticketmaster', JPATH_ADMINISTRATOR, 'en-GB', true);







    //           require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'special.tickets.class.php');
    //           require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'createtickets.class.php');
      


		$path_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'helpers'.DS.'get.amount.php';
		require_once( $path_include );

		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'create.invoice.class.php');

		$file_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'functions.php';
		require_once( $file_include );	

              JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'tables');

		 require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'pdf'.DS.'fpdf'.DS.'fpdf.php');
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'pdf'.DS.'fpdi_ean13.php');

             

             // JPluginHelper::importPlugin('system');










// THIS IS ABSOLUTELY NEEDED FOR BE ABLE TO CREATE BARCODE & HAVE NOT EMPTY VALUE FOR IT
//$joomla_mainframe = JFactory::getApplication('site');                           //  use one of both
$joomla_mainframe = JFactory::getApplication('administrator');








//$joomla_mainframe->initialise();                                                               // generate php error

//$session = JFactory::getSession();                                                             // generate php error

//$barcode = $session->get('barcode');                                                     // generate php error

//$session->set('session_variable_name', $variable_value);        // generate php error







     


    







   //Process with the CSV file content
   $file = fopen(dirname(__FILE__).'/csv_answer_GetMerchantTxns.php', 'r');  

   $data = fgetcsv($file, 5000000, ";"); //Remove if CSV file does not have column headings

      while (($line = fgetcsv($file, 5000000, ";")) !== FALSE) {





                   
			if(function_exists('get_magic_quotes_gpc') and get_magic_quotes_gpc()){
	
            $dragonpay_ws_refNo = urlencode(stripslashes($line[0]));
            $dragonpay_ws_refDate = urlencode(stripslashes($line[1]));
            $dragonpay_ws_merchantId = urlencode(stripslashes($line[2]));
            $dragonpay_ws_merchantTxnId = urlencode(stripslashes($line[3]));
            $dragonpay_ws_amount = urlencode(stripslashes($line[4]));
            $dragonpay_ws_currency = urlencode(stripslashes($line[5]));
            $dragonpay_ws_description = urlencode(stripslashes($line[6]));
            $dragonpay_ws_email = $line[7];
            $dragonpay_ws_status = urlencode(stripslashes($line[8]));
            $dragonpay_ws_procId = urlencode(stripslashes($line[9]));
            $dragonpay_ws_procMsg = urlencode(stripslashes($line[10]));
            $dragonpay_ws_billerId = urlencode(stripslashes($line[11]));
            $dragonpay_ws_settleDate  = urlencode(stripslashes($line[12]));

                     }


                    else {	    

            $dragonpay_ws_refNo = urlencode($line[0]);
            $dragonpay_ws_refDate = urlencode($line[1]);
            $dragonpay_ws_merchantId = urlencode($line[2]);
            $dragonpay_ws_merchantTxnId = urlencode($line[3]);
            $dragonpay_ws_amount = urlencode($line[4]);
            $dragonpay_ws_currency = urlencode($line[5]);
            $dragonpay_ws_description = urlencode($line[6]);
            $dragonpay_ws_email = $line[7];
            $dragonpay_ws_status = urlencode($line[8]);
            $dragonpay_ws_procId = urlencode($line[9]);
            $dragonpay_ws_procMsg = urlencode($line[10]);
            $dragonpay_ws_billerId = urlencode($line[11]);
            $dragonpay_ws_settleDate  = urlencode($line[12]);
			}









             // START:     process for each line

                                          // BY DESIGN TRANSACTION TABLE ONLY RECORD SUCCESS TRANSACTIONS
                    ## if transaction is "S" (Success)
                    if ($dragonpay_ws_status == 'S'){

   
	
				## Connecting the database
				$db = JFactory::getDBO();
				## Current date for database.
				$trans_date = date("d-m-Y H:i");
		
				



                                ## Check that $dragonpay_ws_refNo has not been previously processed in transactions table
                                 ## Only transaction with status "S" are stored there
				$sql = "SELECT COUNT(pid) AS total
						FROM #__ticketmaster_transactions
						WHERE transid = '$dragonpay_ws_refNo'";
		
				$db->setQuery($sql);
				$results = $db->loadObject();
		                
				if($results->total < 1){

                                    // for test purpose
                                     echo "Find one new Transaction ".$dragonpay_ws_refNo." with status S". PHP_EOL;
		                     

                                    



                                            // check if there is/are order(s) for this transaction
                                            $sql = "SELECT COUNT(orderid) AS total FROM #__ticketmaster_orders WHERE ordercode = '$dragonpay_ws_merchantTxnId'";
		
				            $db->setQuery($sql);
				            $results = $db->loadObject();
		
				            if($results->total < 1){

                                                 // no order for this transaction

                                                 // For test purpose
                                                 echo "No update needed: there is no order for transaction ".$dragonpay_ws_refNo. PHP_EOL;

                                                 continue; //go to above "while" (go to next csv $line in the while loop)
                                             }










					## Including required paths to calculator.
					$path_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'helpers'.DS.'get.amount.php';
					include_once( $path_include );
		
					
	                




                                    ## find userid
                                 
                                      //  $db = JFactory::getDBO();   # this line is optional, it's can work without
                                      //  $sql = $db->getQuery(true); # this line is optional, it's can work without
                                       
                                        $sql = "SELECT userid FROM #__ticketmaster_orders WHERE ordercode = '$dragonpay_ws_merchantTxnId'";
		                       
                                        $db->setQuery($sql);

		                        $userid = $db->loadResult();
	            


		                    
   
                                    ## find user email
                                         
		                        $user_email = $dragonpay_ws_email;


                                   

                                    ## Total paid amount for this order                              
                                    $order_total_paid_amount = $dragonpay_ws_amount; 


                     



     
                                                 






		
					
									
					JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'tables');
					$row = JTable::getInstance('transaction', 'Table');
		
					## Pickup All the transaction detail separate with ";"
					$payment_details = $dragonpay_ws_refNo.';'.$dragonpay_ws_refDate.';'.$dragonpay_ws_merchantId.';'.$dragonpay_ws_merchantTxnId.';'.$dragonpay_ws_amount.';'.$dragonpay_ws_currency.';'.$dragonpay_ws_description.';'.$dragonpay_ws_email.';'.$dragonpay_ws_status.';'.$dragonpay_ws_procId.';'.$dragonpay_ws_procMsg.';'.$dragonpay_ws_billerId.';'.$dragonpay_ws_settleDate;
										
									
					## Now store all data in the transactions table
                                         ##  only transaction with status "S" are stored there
					$row->transid 	= $dragonpay_ws_refNo;
					$row->details 	= $payment_details;
					$row->amount 	= $order_total_paid_amount;
									
										
					$row->orderid	   = $dragonpay_ws_merchantTxnId;
                                        $row->userid	   = $userid;
                                        $row->type         = 'Dragonpay';
                                        $row->email_paypal = $user_email;
									
					# Store data
					$row->store();
			
					$query = "UPDATE #__ticketmaster_orders SET paid = 1, published = 1 WHERE ordercode = '$dragonpay_ws_merchantTxnId'";
									
					## Do the query now
					$db->setQuery( $query );
									
					## When query goes wrong.. Show message with error.
					if (!$db->query()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
		













					## OK CHECKS DONE AND WE CAN NOW CREATE THE TICKETS ##
									
					## Include the confirmation class to sent the tickets.
					$path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'createtickets.class.php';
					$override = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'override'.DS.'createtickets.class.php';
			
			
					$query = "SELECT * FROM #__ticketmaster_orders WHERE ordercode = '$dragonpay_ws_merchantTxnId'";
									
					## Do the query now
					$db->setQuery($query);
					$ordered_tickets = $db->loadObjectList();

                                        									
					## Loop through the ordered tickets and create them one by one.
                                        $k = 0;

					for ($i = 0, $n = count($ordered_tickets); $i < $n; $i++ ){
									
							$row  = &$ordered_tickets[$i];
									
							## Check if the override is there.
							if (file_exists($override)) {
								## Yes, now we use it.
								require_once($override);
							} else {
								## No, use the standard
								require_once($path);
							}
		
								$creator = new ticketcreator( (int)$row->orderid );
								$creator->doPDF();
                                                      
                                                        $k=1 - $k;
							
		
					}
		
				












                                         ## Ticket have been created now -- let's send them to the customer.
					## Include the confirmation class to sent the tickets.
					$path_include = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'sendonpayment.class.php';
					include_once( $path_include );
										
					## Sending the ticket immediatly to the client.
					$creator = new sendonpayment( $dragonpay_ws_merchantTxnId );
					$creator->send();
		
					## Done!! No need to do any extra as this will be handled by the plugin.
					## Client will receive an email with their tickets.
						
					## You may test the functionality or receive an email by uncommenting this lines below.
					## To run this tests, please make sure you change the email addresses.












    date_default_timezone_set('Asia/Manila');
    $date = new DateTime();
   //  echo $date->format('Y-m-d\TH:i:s');  // to get DateTime in format requested by Dragonpay 2014-09-03T00:00:00
   // echo $date->format('Y-m-d');      //  this can work also with Dragonpay, but it's less precise







						
					
                                
                                  echo $date->format('Y-m-d\TH:i:s')."   order: ".$dragonpay_ws_merchantTxnId."  : New succesfull transaction inserted in db, order updated, ticket created & send by email". PHP_EOL;


                                  $update_transac = $date->format('Y-m-d\TH:i:s').'   order: '.$dragonpay_ws_merchantTxnId.'  : New succesfull transaction inserted in db, order updated, ticket created & send by email';




                                  ## writte above variable value in a file(s) for check purpose

                                    

 
                                 // new_transactions_log.txt 
                                    // no confidential data here, so .txt is ok
                                $fp = fopen(dirname(__FILE__).'/new_transactions_log.txt', 'a');                                        
                                      fwrite($fp, $update_transac."\n");                                      
                                 fclose($fp);  
                           
      



                               // add also new transaction updated in the debug log.txt
                                   if ($debug_mode == 1) {
                                   // log.txt 
                                      // no confidential data here, so .txt is ok
                                      $fp = fopen(dirname(__FILE__).'/log.txt', 'a');                                        
                                            fwrite($fp, $update_transac."\n");                                       
                                      fclose($fp);  
                                   }





      				
										
       		} ## END:   if($results->total < 1)
						
			






       }  ## END:  if transaction is "S" (Success)




                  // other status case only proceded from IPN data reception, see explain there  






           // END:       process for each line
     





   } // END:    While

fclose($file);





?>
