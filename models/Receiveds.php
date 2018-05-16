<?php
	namespace models;
	/**
     * Cette classe gère les accès bdd pour les receivedes
	 */
	class Receiveds extends \Model
    {
        /**
         * Retourne une entrée par son id
         * @param int $id : L'id de l'entrée
         * @return array : L'entrée
         */
        public function get_by_id ($id)
        {
            $receiveds = $this->getFromTableWhere('receiveds', ['id' => $id]);
            return isset($receiveds[0]) ? $receiveds[0] : false;
        }
        
        /**
         * Retourne une entrée par son numéro
         * @param string $number : le numéro qui a envoyé le message
         * @return array : L'entrée
         */
        public function get_by_send_by ($number)
        {
            $receiveds = $this->getFromTableWhere('receiveds', ['send_by' => $number]);
            return isset($receiveds[0]) ? $receiveds[0] : false;
        }

		/**
		 * Retourne une liste de receivedes sous forme d'un tableau
         * @param int $limit : Nombre de résultat maximum à retourner
         * @param int $offset : Nombre de résultat à ingnorer
		 */
		public function get_list ($limit, $offset)
        {
            $receiveds = $this->getFromTableWhere('receiveds', [], '', false, $limit, $offset);

	    	return $receiveds;
		}

        /**
         * Cette fonction retourne les X dernières entrées triées par date
         * @param int $nb_entry : Nombre d'entrée à retourner
         * @return array : Les dernières entrées
         */
        public function get_lasts_by_date ($nb_entry)
        {
            $receiveds = $this->getFromTableWhere('receiveds', [], 'at', true, $nb_entry);
            return $receiveds;
        }
        
        /**
         * Cette fonction retourne une liste des receiveds sous forme d'un tableau
         * @param string $send_by : Le numéro depuis lequel est envoyé le message
         * @return array : La liste des receiveds
         */	
		public static function get_by_send_by ($send_by)
		{
            $receiveds = $this->getFromTableWhere('receiveds', ['send_by' => $send_by]);
            return $receiveds;
        }

        
        /**
		 * Retourne une liste de receivedes sous forme d'un tableau
         * @param array $ids : un ou plusieurs id d'entrées à récupérer
         * @return array : La liste des entrées
		 */
        public function get_by_ids ($ids)
        {
			$query = " 
                SELECT * FROM receiveds
                WHERE id ";
     
            //On génère la clause IN et les paramètres adaptés depuis le tableau des id 
            $generated_in = $this->generateInFromArray($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->runQuery($query, $params);
        }
        /**
		 * Retourne une liste de receivedes sous forme d'un tableau
         * @param array $ids : un ou plusieurs id d'entrées à supprimer
         * @return int : Le nombre de lignes supprimées
		 */
        public function delete_by_ids ($ids)
        {
			$query = " 
                DELETE FROM receiveds
                WHERE id ";
     
            //On génère la clause IN et les paramètres adaptés depuis le tableau des id 
            $generated_in = $this->generateInFromArray($ids);
            $query .= $generated_in['QUERY'];
            $params = $generated_in['PARAMS'];

            return $this->runQuery($query, $params, self::ROWCOUNT);
        }

        /**
         * Insert une receivede
         * @param array $received : La receivede à insérer avec les champs name, script, admin & admin
         * @return mixed bool|int : false si echec, sinon l'id de la nouvelle lignée insérée
         */
        public function insert ($received)
        {
            $result = $this->insertIntoTable('receiveds', $receiveds);

            if (!$result)
            {
                return false;
            }

            return $this->lastId();
        }

        /**
         * Met à jour une receivede par son id
         * @param int $id : L'id de la received à modifier
         * @param array $received : Les données à mettre à jour pour la receivede
         * @return int : le nombre de ligne modifiées
         */
        public function update ($id, $received)
        {
            return $this->updateTableWhere('receiveds', $received, ['id' => $id]);
        }
        
        /**
         * Compte le nombre d'entrées dans la table
         * @return int : Le nombre d'entrées
         */
        public function count ()
        {
            return $this->countTable('receiveds');
        }

        /**
         * Récupère le nombre de SMS envoyés pour chaque jour depuis une date
         * @param DateTime $date : La date depuis laquelle on veux les SMS
         * @return array : Tableau avec le nombre de SMS depuis la date
		 */
		public function count_by_day_since ($date)
        {
            $query = " 
                SELECT COUNT(id) as nb, DATE_FORMAT(at, '%Y-%m-%d') as at_ymd
                FROM receiveds
                WHERE at > :date
                GROUP BY at_ymd
            ";

            $params = array(
                'date' => $date,
            );

            return $this->runQuery($query, $params);
        }

		/**
         * Cette fonction retourne toutes les discussions, càd les numéros pour lesquels ont a a la fois un message et une réponse
         */	
        public function get_discussions ()
        {
			$query = " 
                    SELECT MAX(at) as at, number
                    FROM (SELECT at, target as number FROM sendeds UNION (SELECT at, send_by as number FROM receiveds)) as discussions
                    GROUP BY number
                    ORDER BY at DESC
            ";

            return $this->runQuery($query);
        }
        
        /**
         * Récupère les SMS reçus depuis une date
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function get_since_by_date ($date)
        {
            $query = " 
                SELECT *
                FROM receiveds
                WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
                ORDER BY at ASC
            ";

            $params = array(
                'date' => $date,
            );

        	return $this->runQuery($query, $params);
        }

		/**
         * Récupère les SMS reçus depuis une date pour un numero
         * @param $date : La date depuis laquelle on veux les SMS (au format 2014-10-25 20:10:05)
         * @param $number : Le numéro
         * @return array : Tableau avec tous les SMS depuis la date
         */
        public function get_since_for_number_by_date ($date, $number)
        {
            $query = " 
                SELECT *
                FROM receiveds
                WHERE at > STR_TO_DATE(:date, '%Y-%m-%d %h:%i:%s')
                AND send_by = :number
                ORDER BY at ASC
            ";

            $params = array(
                'date' => $date,
                'number' => $number
            );

        	return $this->runQuery($query, $params);
        }
    }
