<?php

namespace App\Controllers;
use Core\Http\Controllers\Controller;
use Core\Http\Request;
use Lib\FlashMessage;
use App\Models\Pallet;


class PalletsController extends Controller{

   public function new():void
   {
         $title = "Cadastro de Pallets";
         $newPallet =  new Pallet();

         $this->render('pallets/create',compact('title','newPallet'));
   }

   public function show():void
   {
            $this->retrievePallet();

   }    

   public function registerPallet(Request $request):void
   {
        $params = $request->getParam('pallet');

        $newPallet = new Pallet($params);

        if(!$newPallet->isValid()){
            $title = "Cadastro de Pallets";
            $this->render('pallets/create',compact('title','newPallet'));
            return;
        }

        if(Pallet::exists(['length' => $newPallet->length, 'width' => $newPallet->width])){
            FlashMessage::danger('Pallet já cadastrado');
            $this->redirectTo(route('pallets.form'));
        }
        
        $newPallet->convertToInt('amount');
        $newPallet->convertToInt('minimum_amount');

        if($newPallet->save()){
            FlashMessage::success('Pallet registrado com sucesso!');
            $this->redirectTo(route('root'));
        }

        else{
            FlashMessage::danger('falha ao cadastrar pallet');
            $this->redirectTo(route('pallets.form'));
        }
   }

   public function retrievePallet():void{
         $pallets = Pallet::all();   
         $title = "Estoque Virtual";

        if($pallets){   
            $this->render('pallets/show',compact('title', 'pallets'));
            return;
        }

        $pallets = [];
        $this->render('pallets/show',compact('title','pallets'));

   }


}

?>