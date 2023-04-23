<?php

/*
 *	This idea is when a different part of the app inserts a record
 *	into the Master table, we would then post the data to Wordpress
 *	Watches for:
 *		NOTIFY_MASTER_INSERT
 *		NOTIFY_MASTER_UPDATE
 *		NOTIFY_WORDPRESS_INSERT	//NEW 201704 - send to remote WORDPRESS when local is updated
 *
 *	Should also figure out how to update a session_data page so that
 *	udpates to the Master table are also updated to Wordpress
 *
 *	Triggers:
 *		NOTIFY_WORDPRESS_IMAGE_UPLOADED
 *		NOTIFY_WORDPRESS_POST
 */

require_once( dirname( __FILE__ ) . '/../../class.base.php' );

//class post2session_data extends controller {
class post2session_data extends base {
	var $master_class;		//The class holding the data that was inserted, ready to be posted
	var $data;			//data passed in by notifyer
	
	function __construct( $dispatcher )
	{
		parent::__construct( $dispatcher );
		$this->ObserverRegister( $this, "NOTIFY_MASTER_INSERT", 1 );
		$this->ObserverRegister( $this, "NOTIFY_MASTER_UPDATE", 1 );
		$this->ObserverRegister( $this, "NOTIFY_WORDPRESS_INSERT", 1 );
		$this->ObserverRegister( $this, "NOTIFY_INSERT_UPC", 1 );
		$this->ObserverRegister( $this, "NOTIFY_DETAILS_SET", 1 );
		$this->ObserverRegister( $this, "NOTIFY_MAIN_VALIDATE_MERGE", 1 );
		//$this->ObserverRegister( $this, "NOTIFY_MASTER_UPDATE", 1 );

	}
 	function notified( $class, $event, $msg )
        {
		//echo __FILE__ . ":" . __LINE__ . "<br />\n";
		//var_dump( $class );
			$this->master_class = $class;
			$this->data = $msg;
			$ret = $this->Data2Session();
	}
	function Data2Session()
	{
		if( !isset( $this->master_class ) )
			return;
		if( isset( $this->master_class->details ) )
		{
			//kalli_data class
			foreach( $this->master_class->details as $var => $value )
			{
				$_SESSION[$var] = $value;
			}
		}
		else
		{
			//any other
			foreach( $this->master_class as $var => $value )
			{
				$_SESSION[$var] = $value;
			}
		}
		
	}
}

?>
