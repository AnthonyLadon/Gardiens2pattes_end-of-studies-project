<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230530125403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'creation des tables de la base de données + relations';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresses (id INT AUTO_INCREMENT NOT NULL, localite VARCHAR(255) DEFAULT NULL, commune VARCHAR(255) DEFAULT NULL, code_postal VARCHAR(255) DEFAULT NULL, rue VARCHAR(255) NOT NULL, numero INT NOT NULL, longitude VARCHAR(255) DEFAULT NULL, latitude VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE animaux (id INT AUTO_INCREMENT NOT NULL, maitre_id INT NOT NULL, categorie_animal_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, age INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, poids INT DEFAULT NULL, antecedents_medicaux LONGTEXT DEFAULT NULL, sociabilite VARCHAR(255) DEFAULT NULL, INDEX IDX_9ABE194DCF133C25 (maitre_id), INDEX IDX_9ABE194D23C92311 (categorie_animal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categories_animaux (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, exotique TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commentaires (id INT AUTO_INCREMENT NOT NULL, prestataires_id INT NOT NULL, maitre_id INT NOT NULL, titre VARCHAR(255) NOT NULL, commentaire LONGTEXT NOT NULL, date DATE NOT NULL, en_avant TINYINT(1) DEFAULT NULL, reponse_gardien LONGTEXT DEFAULT NULL, INDEX IDX_D9BEC0C4B2CAA6B8 (prestataires_id), INDEX IDX_D9BEC0C4CF133C25 (maitre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE favoris (id INT AUTO_INCREMENT NOT NULL, maitres_id INT NOT NULL, prestataire_id INT NOT NULL, INDEX IDX_8933C432ACBA7A5E (maitres_id), INDEX IDX_8933C432BE3DB2B7 (prestataire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE images (id INT AUTO_INCREMENT NOT NULL, maitre_id INT DEFAULT NULL, prestataire_id INT DEFAULT NULL, animal_id INT DEFAULT NULL, gallerie_gardien_id INT DEFAULT NULL, gallerie_maitre_id INT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, home_carrousel TINYINT(1) DEFAULT NULL, INDEX IDX_E01FBE6ACF133C25 (maitre_id), INDEX IDX_E01FBE6ABE3DB2B7 (prestataire_id), INDEX IDX_E01FBE6A8E962C16 (animal_id), INDEX IDX_E01FBE6AF6ABE73D (gallerie_gardien_id), INDEX IDX_E01FBE6A2BBE925A (gallerie_maitre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE indisponibilites (id INT AUTO_INCREMENT NOT NULL, prestataires_id INT DEFAULT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, INDEX IDX_C684A4C1B2CAA6B8 (prestataires_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE maitres (id INT AUTO_INCREMENT NOT NULL, bio LONGTEXT DEFAULT NULL, newsletter TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messages (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, recepient_id INT NOT NULL, date_creation DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, message LONGTEXT NOT NULL, INDEX IDX_DB021E96F624B39D (sender_id), INDEX IDX_DB021E96F1B7C6C (recepient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE newsletters (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, date_creation DATE NOT NULL, is_sent TINYINT(1) NOT NULL, date_envoi DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notes_gardien (id INT AUTO_INCREMENT NOT NULL, gardien_id INT NOT NULL, maitre_id INT NOT NULL, note INT NOT NULL, INDEX IDX_D2D3CD5736F5263E (gardien_id), INDEX IDX_D2D3CD57CF133C25 (maitre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prestataires (id INT AUTO_INCREMENT NOT NULL, tva_num INT DEFAULT NULL, garde_domicile TINYINT(1) NOT NULL, vehicule TINYINT(1) NOT NULL, jardin TINYINT(1) NOT NULL, tarif INT DEFAULT NULL, bio LONGTEXT DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, soins_veto TINYINT(1) DEFAULT NULL, zone_gardiennage INT DEFAULT NULL, tarif_deplacement INT DEFAULT NULL, tarif_promenade INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prestataires_categories_animaux (prestataires_id INT NOT NULL, categories_animaux_id INT NOT NULL, INDEX IDX_CBCD7EFDB2CAA6B8 (prestataires_id), INDEX IDX_CBCD7EFD854ACA2 (categories_animaux_id), PRIMARY KEY(prestataires_id, categories_animaux_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, gardien_id INT NOT NULL, maitre_id INT NOT NULL, animal_id INT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, details LONGTEXT DEFAULT NULL, validation_prestataire TINYINT(1) NOT NULL, paiement_ok TINYINT(1) NOT NULL, nb_passages INT DEFAULT NULL, prix_total INT DEFAULT NULL, hebergement TINYINT(1) DEFAULT NULL, date_paiement DATETIME DEFAULT NULL, nb_promenades INT DEFAULT NULL, INDEX IDX_42C8495536F5263E (gardien_id), INDEX IDX_42C84955CF133C25 (maitre_id), INDEX IDX_42C849558E962C16 (animal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE signalement_abus (id INT AUTO_INCREMENT NOT NULL, prestataires_id INT DEFAULT NULL, commentaire_id INT DEFAULT NULL, date DATE NOT NULL, est_traite TINYINT(1) NOT NULL, INDEX IDX_7A78CCDDB2CAA6B8 (prestataires_id), INDEX IDX_7A78CCDDBA9CD190 (commentaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateurs (id INT AUTO_INCREMENT NOT NULL, maitre_id INT DEFAULT NULL, prestataire_id INT DEFAULT NULL, adresse_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, pseudo VARCHAR(255) DEFAULT NULL, email VARCHAR(180) NOT NULL, tel_num INT DEFAULT NULL, banni TINYINT(1) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) DEFAULT NULL, is_verified TINYINT(1) NOT NULL, date_inscription DATETIME DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_497B315EE7927C74 (email), UNIQUE INDEX UNIQ_497B315ECF133C25 (maitre_id), UNIQUE INDEX UNIQ_497B315EBE3DB2B7 (prestataire_id), INDEX IDX_497B315E4DE7DC5C (adresse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE animaux ADD CONSTRAINT FK_9ABE194DCF133C25 FOREIGN KEY (maitre_id) REFERENCES maitres (id)');
        $this->addSql('ALTER TABLE animaux ADD CONSTRAINT FK_9ABE194D23C92311 FOREIGN KEY (categorie_animal_id) REFERENCES categories_animaux (id)');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C4B2CAA6B8 FOREIGN KEY (prestataires_id) REFERENCES prestataires (id)');
        $this->addSql('ALTER TABLE commentaires ADD CONSTRAINT FK_D9BEC0C4CF133C25 FOREIGN KEY (maitre_id) REFERENCES maitres (id)');
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C432ACBA7A5E FOREIGN KEY (maitres_id) REFERENCES maitres (id)');
        $this->addSql('ALTER TABLE favoris ADD CONSTRAINT FK_8933C432BE3DB2B7 FOREIGN KEY (prestataire_id) REFERENCES prestataires (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6ACF133C25 FOREIGN KEY (maitre_id) REFERENCES maitres (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6ABE3DB2B7 FOREIGN KEY (prestataire_id) REFERENCES prestataires (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6A8E962C16 FOREIGN KEY (animal_id) REFERENCES animaux (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6AF6ABE73D FOREIGN KEY (gallerie_gardien_id) REFERENCES prestataires (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6A2BBE925A FOREIGN KEY (gallerie_maitre_id) REFERENCES maitres (id)');
        $this->addSql('ALTER TABLE indisponibilites ADD CONSTRAINT FK_C684A4C1B2CAA6B8 FOREIGN KEY (prestataires_id) REFERENCES prestataires (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F624B39D FOREIGN KEY (sender_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F1B7C6C FOREIGN KEY (recepient_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE notes_gardien ADD CONSTRAINT FK_D2D3CD5736F5263E FOREIGN KEY (gardien_id) REFERENCES prestataires (id)');
        $this->addSql('ALTER TABLE notes_gardien ADD CONSTRAINT FK_D2D3CD57CF133C25 FOREIGN KEY (maitre_id) REFERENCES maitres (id)');
        $this->addSql('ALTER TABLE prestataires_categories_animaux ADD CONSTRAINT FK_CBCD7EFDB2CAA6B8 FOREIGN KEY (prestataires_id) REFERENCES prestataires (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE prestataires_categories_animaux ADD CONSTRAINT FK_CBCD7EFD854ACA2 FOREIGN KEY (categories_animaux_id) REFERENCES categories_animaux (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495536F5263E FOREIGN KEY (gardien_id) REFERENCES prestataires (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955CF133C25 FOREIGN KEY (maitre_id) REFERENCES maitres (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849558E962C16 FOREIGN KEY (animal_id) REFERENCES animaux (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE signalement_abus ADD CONSTRAINT FK_7A78CCDDB2CAA6B8 FOREIGN KEY (prestataires_id) REFERENCES prestataires (id)');
        $this->addSql('ALTER TABLE signalement_abus ADD CONSTRAINT FK_7A78CCDDBA9CD190 FOREIGN KEY (commentaire_id) REFERENCES commentaires (id)');
        $this->addSql('ALTER TABLE utilisateurs ADD CONSTRAINT FK_497B315ECF133C25 FOREIGN KEY (maitre_id) REFERENCES maitres (id)');
        $this->addSql('ALTER TABLE utilisateurs ADD CONSTRAINT FK_497B315EBE3DB2B7 FOREIGN KEY (prestataire_id) REFERENCES prestataires (id)');
        $this->addSql('ALTER TABLE utilisateurs ADD CONSTRAINT FK_497B315E4DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresses (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE animaux DROP FOREIGN KEY FK_9ABE194DCF133C25');
        $this->addSql('ALTER TABLE animaux DROP FOREIGN KEY FK_9ABE194D23C92311');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C4B2CAA6B8');
        $this->addSql('ALTER TABLE commentaires DROP FOREIGN KEY FK_D9BEC0C4CF133C25');
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432ACBA7A5E');
        $this->addSql('ALTER TABLE favoris DROP FOREIGN KEY FK_8933C432BE3DB2B7');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6ACF133C25');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6ABE3DB2B7');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6A8E962C16');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6AF6ABE73D');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6A2BBE925A');
        $this->addSql('ALTER TABLE indisponibilites DROP FOREIGN KEY FK_C684A4C1B2CAA6B8');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F624B39D');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F1B7C6C');
        $this->addSql('ALTER TABLE notes_gardien DROP FOREIGN KEY FK_D2D3CD5736F5263E');
        $this->addSql('ALTER TABLE notes_gardien DROP FOREIGN KEY FK_D2D3CD57CF133C25');
        $this->addSql('ALTER TABLE prestataires_categories_animaux DROP FOREIGN KEY FK_CBCD7EFDB2CAA6B8');
        $this->addSql('ALTER TABLE prestataires_categories_animaux DROP FOREIGN KEY FK_CBCD7EFD854ACA2');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495536F5263E');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955CF133C25');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849558E962C16');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE signalement_abus DROP FOREIGN KEY FK_7A78CCDDB2CAA6B8');
        $this->addSql('ALTER TABLE signalement_abus DROP FOREIGN KEY FK_7A78CCDDBA9CD190');
        $this->addSql('ALTER TABLE utilisateurs DROP FOREIGN KEY FK_497B315ECF133C25');
        $this->addSql('ALTER TABLE utilisateurs DROP FOREIGN KEY FK_497B315EBE3DB2B7');
        $this->addSql('ALTER TABLE utilisateurs DROP FOREIGN KEY FK_497B315E4DE7DC5C');
        $this->addSql('DROP TABLE adresses');
        $this->addSql('DROP TABLE animaux');
        $this->addSql('DROP TABLE categories_animaux');
        $this->addSql('DROP TABLE commentaires');
        $this->addSql('DROP TABLE favoris');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE indisponibilites');
        $this->addSql('DROP TABLE maitres');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE newsletters');
        $this->addSql('DROP TABLE notes_gardien');
        $this->addSql('DROP TABLE prestataires');
        $this->addSql('DROP TABLE prestataires_categories_animaux');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE signalement_abus');
        $this->addSql('DROP TABLE utilisateurs');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
