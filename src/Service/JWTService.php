<?php
//ce fichier va nous permettre de mettre une aauthentication pour savoir si le liens est toujours valide

namespace App\Service;

use DateTimeImmutable;
use phpDocumentor\Reflection\Types\Boolean;

class JWTService
{
    /**
     * generation du jwt(json web token)
     *  @param array $header
     * @param array $payload
     * @param string $secret
     * @param int $validity
     * @param string  
     */

    //on genere le token(message),les 10800 qui correspond a 3h est la duree du message.Plus d'infos aller sur JWT.com

    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        if ($validity > 0) {
            
            $now = new DateTimeImmutable();

            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();

            $payload['exp'] = $exp;
        }

        //on encode base64(format json)

        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        //on nettoie les valeurs encodés(retrait des +,/ et =)
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);

        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        //on genere la signature
        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);

        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        //on cree le token
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;



        return $jwt;
    }
    //on verifie que le token est valid (correctemenent formé)
    public function isValid(string $token): bool
    {
        return preg_match('/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/', $token) === 1;
    }

    //on recupere le payload pour svoir si le token a expire

    public function getPayload(string $token): array
    {
        //on separe le token
        $array = explode('.', $token);

        //on decode le payload
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    //on verifie si le token a expiré

    public function isExpired(string $token): Bool
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();
         
        return $payload['exp'] < $now->getTimestamp();
    }

    //on recupere le header 

    public function getHeader(string $token): array
    {
        //on demonte le token
        $array = explode('.', $token);

        //on decode le header
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }


    //on verifie la signature du token
    public function check(string $token, string $secret)
    {
        //on recupere le header et le payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        //on regenre un token et on met 0 pour pas regenerer les date d'expiration 
        $verifToken = $this->generate($header, $payload, $secret, 0);

        //si les deux token ont les memes signature et egaux alors on a les memes contenu et que le token n'est pas corrompu

        return $token === $verifToken;
    }
}
