<?php

namespace App\Controllers;
use Core\Http\Controllers\Controller;
use Core\Http\Request;


class PalletsController extends Controller{

   public function new():void
   {
         $title = "Cadastro de Pallets";

         $this->render('pallets/create',compact('title'));
   }

   public function registerPallet(Request $request):void{
        $params = $request->getParams('pallet')
   }


}

?>