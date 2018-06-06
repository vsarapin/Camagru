<?php

namespace application\models;

use application\controllers\ErrorController;
use application\core\base\Model;

class Other extends Model {

    public function makeImage($imageBaseCode){
        $path = ROOT . "/public/png";
        $outputFile = md5(uniqid(rand(),1)) . ".png";
        $ifp = fopen($path . "/" . $outputFile, 'wb');
        $data = explode( ',', $imageBaseCode );
        fwrite($ifp, base64_decode($data[1]));
        fclose($ifp);
        $tmpArray = ["/png/" . $outputFile];
        $this->insertOne($tmpArray);
        $imgSmall = 'matrixheroes1.png'; //Тут надо доелать, указыватьб фото не ручками
        $img1 = imagecreatefrompng($path . DIRECTORY_SEPARATOR . $outputFile);
        $img2 = imagecreatefrompng($path . DIRECTORY_SEPARATOR . $imgSmall);
        if($img1 && $img2) {
            $x2 = imagesx($img2);
            $y2 = imagesy($img2);
            imagecopyresampled($img1, $img2, 20, 20, 0, 0, $x2, $y2, $x2, $y2);
            imagepng($img1, $path . "/" . $outputFile, 9);
            header('Location: /camagru/');
        } else {
            ErrorController::errorPage();
        }
    }

    public function showAllPhoto() {
        $photo = '';
        $this->table = 'images';
        $tmpArray = $this->findAll();
        foreach ($tmpArray as $key) {
            foreach ($key as $src => $img) {
                if (file_exists(WWW . "/" . $img)) {
                    $photo = $photo . "<img src=" ."\"". $img . "\">";
                }
            }
        }
        return $photo;
    }
}