<?php

/**
 *  API pour récupérer les stats d'un serveur ainsi que sa description et le logo sur 
 *  RPG Paradize
 *  @author KahacF
 */

Class PriDofRPG {

    // C'est privée donc pourquoi tu regarde?
    private $id;
    private $html;
    private $position;
    private $votes;
    private $out;

    // Requête d'Accès
    private function acces() {
        $url = 'http://www.rpg-paradize.com/site--'.$this->id;
        $agent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.104 Safari/537.36';
        $cookie = @tempnam ( '', 'cookie_' );
        if ($cookie === false)
            $cookie = @tempnam ( sys_get_temp_dir(), 'cookie_' );
        if ($cookie === false)
            $cookie = 'cookie.txt';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate,sdch");
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_COOKIESESSION, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    // Fonction nommé TextBetween(txtbtwn) pour le reste
    private function  txtbtwn($data, $tagOpen, $tagClose) {
        $startIn = strpos($data, $tagOpen) + strlen($tagOpen);
        $endIn = strpos($data, $tagClose, $startIn);
        $result = substr($data, $startIn, $endIn - $startIn);
        return (empty($result) ? false : $result);
    }

    // Rechargement est récupération pour la suite
    public function web($forceRefresh = true) {
        if (!$forceRefresh and !empty($this->html))
            return true;
        $html = $this->acces();
        if (empty($html))
            throw new Exception('Impossible d\'obtenir la page.');
        $this->html = $html;
        $this->position = intval($this->txtbtwn($html, '<br /><br /><b>Position ', '</b><br><br>Clic Sortant : '));
        $this->votes = intval($this->txtbtwn($html, '">Vote : ', '</a></div></td></tr></table>'));
        $this->cliques = intval($this->txtbtwn($html, '</b><br><br>Clic Sortant : ', '</td></tr></table><hr>'));
        return true;
    }

    // Prévient quand l'ID du serveur est incorrect
    public function __construct($id) {
        if (!ctype_digit((string)$id) or $id < 1)
            throw new Exception('L\'id du serveur est incorrect.');
        $this->id = (string)$id;
    }
    
    // Encode le Web/HTML en JSON
    public function __toString() {
        $this->web(false);
        $json = new stdClass();
        $json->position = $this->position;
        $json->votes = $this->votes;
        $json->cliques = $this->cliques;
        return json_encode($json);
    }
    
    // Récupére un nom
    public function __get($name) {
        $this->web(false);
        if (isset($this->$name))
            return $this->$name;
        return null;
    }
    
    
    // Méthodes utilisables pour l'affichage sur votre site internet !

    // Vous renvoie la position du serveur
    public function khcfPosition() {
        $this->web(false);
        return $this->position;
    }
    

    // Vous renvoie son nombre de vote
    public function khcfVotes() {
        $this->web(false);
        return $this->votes;
    }
    

    // Vous renvoie sont nombre sur clique vers sont site internet
    public function khcfCliques() {
        $this->web(false);
        return $this->cliques;
    }
}

