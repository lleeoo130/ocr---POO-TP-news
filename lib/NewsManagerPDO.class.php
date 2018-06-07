<?php

class NewsManagerPDO extends NewsManager
{
    /**
   * Attribut contenant l'instance représentant la BDD.
   * @type PDO
   */
    protected $db;

    /**
   * Constructeur étant chargé d'enregistrer l'instance de PDO dans l'attribut $db.
   * @param $db PDO Le DAO
   * @return void
   */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
   * @see NewsManager::add()
   */
    
    protected function add(News $news)
    {
        $requete = $this->db->prepare('INSERT INTO news(auteur, titre, contenu, dateAjout, dateModif) VALUES(:auteur, :titre, :contenu, NOW(), NOW())');
    
        $requete->bindValue(':titre', $news->titre());
        $requete->bindValue(':auteur', $news->auteur());
        $requete->bindValue(':contenu', $news->contenu());
        
        $requete->execute();
    }

    public function count()
    {
        return $this->db->query('SELECT COUNT(*) FROM news')->fetchColumn();
    }

    public function delete($id)
    {
        $this->db->query('DELETE FROM news WHERE id = '. (int) $id);
    }

    public function getList($start = -1, $limit= -1)
    {
        $sql = 'SELECT id, auteur, titre, contenu, dateAjout, dateModif FROM news ORDER BY id DESC';

        // On vérifie les paramètres donnés: 
        if (($start != -1) || ($limit != -1))
        {
            $sql .= ' LIMIT '.(int) $limit.' OFFSET '.(int) $start;
        }

        $requete = $this->db->query($sql);
        $requete->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'News');

        $listeNews = $requete->fetchAll();


        // On parcourt notre liste de news pour pouvoir placer des instances de DateTime en guise de dates d'ajout et de modification.
        foreach ($listeNews as $news) 
        {
            $news->setDateAjout( new DateTime($news->dateAjout()));
            $news->setDateModif( new DateTime($news->dateModif()));
        }

        $requete->closeCursor();

        return $listeNews;
    }

    public function getUnique($id)
    {
        $requete = $this->db->prepare('SELECT id, auteur, titre, contenu, dateAjout, dateModif FROM news WHERE id = :id');
        $requete->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $requete->execute();
        
        $requete->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'News');

        $news = $requete->fetch();

        $news->setDateAjout(new DateTime($news->dateAjout()));
        $news->setDateModif(new DateTime($news->dateModif()));
        
        return $news;
    }

    protected function update(News $news)
    {
        $requete = $this->db->prepare('UPDATE news SET auteur = :auteur, titre = :titre, contenu = :contenu, dateModif = NOW() WHERE id = :id');
    
        $requete->bindValue(':titre', $news->titre());
        $requete->bindValue(':auteur', $news->auteur());
        $requete->bindValue(':contenu', $news->contenu());
        $requete->bindValue(':id', $news->id(), PDO::PARAM_INT);
        
        $requete->execute();
    }


}