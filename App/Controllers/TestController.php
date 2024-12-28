<?php
namespace Theincubator\PhpRestApiLite\Controllers;

use Theincubator\PhpRestApiLite\Controller;
/**
 * Description of TestController
 *
 * @author chara
 */
class TestController extends Controller{
    public function test ($param1, $param2) {
        return [ 'param' => $param1.' '.$param2, $this->getData, $this->postData];
    }
}
