<?php

/*
MediaID
MediaName
MediaLogo
MediaStream
MediaFile
MediaDescription
MediaIcon
*/

class media {
    public $id;
    public $name;
    public $logo;
    public $stream;
    public $file;
    public $description;
    public $icon;
}

class model_media extends model {

    public $error;

    public function __construct() {
        parent::__construct();
    }

    public function install() {

        //var_dump($this->db);

        $sql[] = "DROP TABLE IF EXISTS `stb_media_radio`";
        $sql[] = "DROP TABLE IF EXISTS `stb_media_tv`";
        $sql[] = "DROP TABLE IF EXISTS `stb_media_vod`";
        $sql[] = "DROP TABLE IF EXISTS `stb_media`";
        $sql[] = "CREATE TABLE IF NOT EXISTS `stb_media` (
                  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `name` VARCHAR(100) NOT NULL,
                  `description` MEDIUMTEXT NULL,
                  `file` VARCHAR(200) NULL,
                  `stream` VARCHAR(200) NULL,
                  `logo` VARCHAR(200) NULL,
                  `icon` VARCHAR(200) NULL,
                  `status` TINYINT(1) NOT NULL DEFAULT 1,
                  PRIMARY KEY (`id`))
                  ENGINE = InnoDB";
        $sql[] = "CREATE TABLE IF NOT EXISTS `stb_media_radio` (
                  `id` INT(11) UNSIGNED NOT NULL,
                  PRIMARY KEY (`id`),
                  CONSTRAINT `FK_MEDIA_RADIO`
                    FOREIGN KEY (`id`)
                    REFERENCES `stb_media` (`id`)
                    ON DELETE NO ACTION
                    ON UPDATE NO ACTION)
                  ENGINE = InnoDB";
        $sql[] = "CREATE TABLE IF NOT EXISTS `stb_media_tv` (
                  `id` INT(11) UNSIGNED NOT NULL,
                  PRIMARY KEY (`id`),
                  CONSTRAINT `FK_MEDIA_TV`
                    FOREIGN KEY (`id`)
                    REFERENCES `stb_media` (`id`)
                    ON DELETE NO ACTION
                    ON UPDATE NO ACTION)
                  ENGINE = InnoDB";
        $sql[] = "CREATE TABLE IF NOT EXISTS `stb_media_vod` (
                  `id` INT(11) UNSIGNED NOT NULL,
                  PRIMARY KEY (`id`),
                  CONSTRAINT `FK_MEDIA_VOD`
                    FOREIGN KEY (`id`)
                    REFERENCES `stb_media` (`id`)
                    ON DELETE NO ACTION
                    ON UPDATE NO ACTION)
                  ENGINE = InnoDB";
        $s = 0;
        $f = 0;
        foreach($sql as $s) {
            $result = $this->db->query($s); //var_dump($result);
            if(!$this->db->getLastError())
                $s++;
            else $f++;
        }

        if($s) return true;

        $this->error = $this->db->getLastError();
        return false;
    }

    public function save() {
        //var_dump($_POST);

        $data['name'] = $this->db->escape($_POST['name']);
        $data['description'] = $this->db->escape(trim($_POST['description']));
        $data['file'] = $this->db->escape($_POST['file']);
        $data['stream'] = $this->db->escape($_POST['stream']);
        $data['status'] = $this->db->escape($_POST['status']);
        if(isset($_POST['id'])) {
            $where['id'] = $this->db->escape($_POST['id']);
            $result = $this->db->update('stb_media', $data, $where);
            if($result == 1) {
                return true;
            } else {
                if($this->error == '') return true;
                $this->error = $this->db->getLastError();
                return false;
            }
        }
        $result = $this->db->insert('stb_media', $data); //var_dump($this->db); //var_dump($result);
        if($result == 1) {
            $id = $this->db->getLastInsertId();
            $table = '';
            switch($_POST['mediatype']) {
                case 'tv':
                    $table = "stb_media_tv"; break;
                case 'radio':
                    $table = "stb_media_radio"; break;
                case 'vod':
                    $table = 'stb_media_vod'; break;
            }
            $this->db->insert($table, array('id'=>$id));
            return $id;
        } else {
            $this->error = $this->db->getLastError();
            return $result;
        }
    }

    public function getAll($type = null){
        $result = array();
        $sql = "SELECT id, name, description, file, stream, logo, icon, status FROM stb_media m ";
        switch($type) {
            case 'tv':
                $sql .= "WHERE m.id IN (SELECT id FROM stb_media_tv)"; break;
            case 'radio':
                $sql .= "WHERE m.id IN (SELECT id FROM stb_media_radio)"; break;
            case 'vod':
                $sql .= "WHERE m.id IN (SELECT id FROM stb_media_vod)"; break;
        }
        $result = $this->db->query($sql); //var_dump($this->db);
        return $result;
    }

    public function delete($id, $type){
        $data['id'] = $this->db->escape($id);
        $child = false;
        switch($type){
            case 'tv':
                $child = $this->db->delete('stb_media_tv', $data); break;
            case 'radio':
                $child = $this->db->delete('stb_media_radio', $data); break;
            case 'vod':
                $child = $this->db->delete('stb_media_vod', $data); break;
        }
        if($child) {
            $result = $this->db->delete('stb_media', $data);
            if($result) return true;
        }
        $this->error = $this->db->getLastError();
        return false;
    }

    public function get($id = 0) {
        $id = $this->db->escape($id);
        $sql = "SELECT id, name, description, file, stream, logo, icon, status "
            . "FROM stb_media WHERE id = ".$id;
        $media = $this->db->getRow( $sql ); //var_dump($this->db);
        return $media;
    }
}