<?php
// +----------------------------------------------------------------------
// | Router LISTENER [ WE CAN DO IT JUST THINK IT ]

// +----------------------------------------------------------------------
namespace App\Core\Event;

use Phalcon\Db\Profiler;
use Phalcon\Events\Event;
use Phalcon\Logger;
use Phalcon\Http\Request;

class RouterListener
{

    public function beforeDispatch(Event $event,$connection)
    {
		if($_SERVER['REQUEST_URI']=='/index.php'){
			    echo "sorry";
				return false; 
		}
	
    }


}