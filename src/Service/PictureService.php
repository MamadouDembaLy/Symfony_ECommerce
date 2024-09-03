<?php
//service.yaml là ou se trouve le chemin d'upload
namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureService
{
    private $params;

    //function permettant d'aller chercher des informations dans services.yaml
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function add(UploadedFile $picture, ?string $folder = '', ?int $width = 250, ?int $height = 250)
    {
        //on donnes un nouveau nom à l'image

        $fichier = md5(uniqid(rand(), true)) . '.webp'; //on peut aussi choisir .jpg ou png

        //on va recupérer les infos de l'image largeur,hauteur
        $picture_infos = getimagesize($picture);

        if ($picture_infos === false) {
            throw new Exception('format d\'image incorrect');
        }

        //on verifie le format de l'image
        switch ($picture_infos['mime']) {
            case 'image/png':
                $picture_source = imagecreatefrompng($picture);
                break;
            case 'image/jpeg':
                $picture_source = imagecreatefromjpeg($picture);
                break;
            case 'image/jpeg':
                $picture_source = imagecreatefromwebp($picture);
                break;
            default:
                throw new Exception('format d\'image incorrect');
        }
        //on recadre l'image
        //on recupere les dimensions
        $imageWidth = $picture_infos[0];
        $imageHeigth = $picture_infos[1];

        //on verifie l'orientation de l'image
        switch ($imageWidth <=> $imageHeigth) {

            case -1: //portrait
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = ($imageHeigth - $squareSize) / 2;
                break;
            case 0: //carre
                $squareSize = $imageWidth;
                $src_x = 0;
                $src_y = 0;
                break;
            case 1: //paysage
                $squareSize = $imageHeigth;
                $src_x = ($imageWidth - $squareSize) / 2;
                $src_y = 0;
                
                break;
        }

        //on crée une nouvelle image "vierge"
        $resized_picture = imagecreatetruecolor($width,$height);

        imagecopyresampled($resized_picture, $picture_source, 0, 0, $src_x, $src_y, $width, $height, $squareSize, $squareSize);
        $path = $this->params->get('images_directory'). $folder;

        //on cree le dossier de destination s'il n'existe pas
        if(!file_exists($path.'/mini/'))
        {
            mkdir($path.'/mini/',0755,true);
        }

        //on stocke l'image recadré
        imagewebp($resized_picture, $path .'/mini/' . $width . 'x' . $height . '-' . $fichier);

        $picture->move($path .'/', $fichier );

        return $fichier;
    }

    public function delete(string $fichier, ?string $folder = '', ?int $width = 250, ?int $height = 250 )
    {
        if($fichier !== 'default.webp'){
            $success = false;
            $path = $this->params->get('images_directory') . $folder;

            $mini = $path . '/mini/' . $width . 'x' . $height . '-' . $fichier;

            if(file_exists($mini)){
                unlink($mini);
                $success=true;
            }
            $original = $path. '/' .$fichier;

            if(file_exists($original))
            {
                unlink($original);
                $success = true;
            }
            return $success;
        }
        return false;
    }
}
