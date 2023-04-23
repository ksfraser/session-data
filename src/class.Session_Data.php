<?php

/************************************//**
* Module for stashing data into a SESSION variable.
*
********************************************************/

class Session_Data extends origin
{
    	/***************************************************************//**
         *build_interestedin
         *
         *      DEMO function that needs to be overridden
         *      This function builds the table of events that we
         *      want to react to and what handlers we are passing the
         *      data to so we can react.
         * ******************************************************************/
        function build_interestedin()
        {
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
                $this->interestedin['SESSION_SAVE']['function'] = "session_save";
                $this->interestedin['SESSION_QUERY']['function'] = "session_query";
        //      throw new Exception( "You MUST override this function, even if it is empty!", KSF_FCN_NOT_OVERRIDDEN );
        }
	private function save_setting( $var, $val )
	{
		global $_SESSION;
		$_SESSION[$var] = $val;
		return TRUE;
	}

	/*************************************************//**
	* Save a settings value to SESSION
	*
	* If msg is an array, then the 1st field is the variable
	* and the second field the value OR an array of var/val pairs.
	*
	* Otherwise we will assume it is a field name and try 
	* to ->get the value.
	*
	* @param caller Object that triggered the message
	* @pasram msg the value
	* @return bool
	******************************************************/
	function session_save( $caller, $msg )
	{
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
		var_dump( $msg );
		if( is_array( $msg ) )
		{
			if( count( $msg ) == 2 )
			{
				if( is_array( $msg[0] ) )
				{
					foreach( $msg as $arr )
					{
						$this->session_save( $caller, $arr );
					}
				}
				else
				{
					$var = $msg[0];
					$val = $msg[1];
				}
			}
			else
			if( count( $msg ) == 1 )
			{
				foreach( $msg as $key=>$val )
				{
					echo "Key " . $key . " has value " . $val . "\n\r";
					$this->save_setting( $key, $val );
				}
			}
			else
			{
				foreach( $msg as $arr )
				{
					$this->session_save( $caller, $arr );
				}
			}
		}
		else 
		{
			$var = $msg;
		}

		if( ! isset( $val ) AND method_exists( $caller, 'get' ) )
		{
			try {
				$val = $caller->get( $var );
			} catch( Exception $e )
			{
				$msg = $e->getMessage();
				$code = $e->getCode();
				switch( $code )
				{
					case 'KSF_VAR_NOT_SET':
						echo "Var not set//" . $msg . "\n\r";
						break;
					default:
						echo $msg;
				}
			}
		}
		if( isset( $var ) AND isset( $val ) )
		{
			$this->save_setting( $var, $val );
			return TRUE;
		}
		return FALSE;
	}
	/*********************************************//**
	* Extract the setting value, and pass back to caller
	*
	* Assumption the name of the stored value is also 
	* the name of the function to pass the value back to.
	* Of course we could always do a ->set( 'variable', $value )
	* as a fallback
	*
	* @param caller Object that triggered the message
	* @param msg The message (field in this case)
	* @returns bool
	**************************************************/
	function session_query( $caller, $msg )
	{
		echo get_class( $this ) . "::" . __METHOD__ . "\n\r";
		if( isset( $_SESSION[$msg] ) )
		{
			$value =  $_SESSION[$msg] ;
		}
		else
		{
			//Do we have a settings table?
			return FALSE;
		}
		if( method_exists( $caller, $msg ) )
		{
			$caller->$msg( $value );
		}
		else if( method_exists( $caller, set ) )
		{
			$caller->set( $msg, $value );
		}
		$this->tell_eventloop( $this, 'SETTING_' . $msg, $value );
		return TRUE;
	}

}
