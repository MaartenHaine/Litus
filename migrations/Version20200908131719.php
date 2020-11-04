<?php
declare(strict_types=1);

/**
 * Litus is a project by a group of students from the KU Leuven. The goal is to create
 * various applications to support the IT needs of student unions.
 *
 * @author Niels Avonds <niels.avonds@litus.cc>
 * @author Karsten Daemen <karsten.daemen@litus.cc>
 * @author Koen Certyn <koen.certyn@litus.cc>
 * @author Bram Gotink <bram.gotink@litus.cc>
 * @author Dario Incalza <dario.incalza@litus.cc>
 * @author Pieter Maene <pieter.maene@litus.cc>
 * @author Kristof Mariën <kristof.marien@litus.cc>
 * @author Lars Vierbergen <lars.vierbergen@litus.cc>
 * @author Daan Wendelen <daan.wendelen@litus.cc>
 * @author Mathijs Cuppens <mathijs.cuppens@litus.cc>
 * @author Floris Kint <floris.kint@vtk.be>
 *
 * @license http://litus.cc/LICENSE
 */

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Version 20200908131719
 */
class Version20200908131719 extends \Doctrine\Migrations\AbstractMigration
{
    /**
     * @param  \Doctrine\DBAL\Schema\Schema $schema
     * @return void
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE registration_shift_users_people_academic_years_map_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE shift_registration_shifts_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE shift_registration_shifts_registered_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE registration_shift_users_people_academic_years_map (id BIGINT NOT NULL, person BIGINT DEFAULT NULL, academic_year BIGINT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DE7A1E7234DCD176 ON registration_shift_users_people_academic_years_map (person)');
        $this->addSql('CREATE INDEX IDX_DE7A1E72275AE721 ON registration_shift_users_people_academic_years_map (academic_year)');
        $this->addSql('CREATE UNIQUE INDEX registration_shift_users_academic_years_map_person_academic_year ON registration_shift_users_people_academic_years_map (person, academic_year)');
        $this->addSql('CREATE TABLE shift_registration_shifts (id BIGINT NOT NULL, creation_person BIGINT DEFAULT NULL, academic_year BIGINT DEFAULT NULL, unit BIGINT DEFAULT NULL, event BIGINT DEFAULT NULL, location BIGINT DEFAULT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, visible_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, signout_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, nb_registered INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, ticket_needed BOOLEAN DEFAULT \'false\' NOT NULL, members_only BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D32A58FE5A8B0903 ON shift_registration_shifts (creation_person)');
        $this->addSql('CREATE INDEX IDX_D32A58FE275AE721 ON shift_registration_shifts (academic_year)');
        $this->addSql('CREATE INDEX IDX_D32A58FEDCBB0C53 ON shift_registration_shifts (unit)');
        $this->addSql('CREATE INDEX IDX_D32A58FE3BAE0AA7 ON shift_registration_shifts (event)');
        $this->addSql('CREATE INDEX IDX_D32A58FE5E9E89CB ON shift_registration_shifts (location)');
        $this->addSql('CREATE TABLE shift_registration_shifts_registered_map (registrations_shift BIGINT NOT NULL, registered BIGINT NOT NULL, PRIMARY KEY(registrations_shift, registered))');
        $this->addSql('CREATE INDEX IDX_E971DA77897044DB ON shift_registration_shifts_registered_map (registrations_shift)');
        $this->addSql('CREATE INDEX IDX_E971DA774BFEE160 ON shift_registration_shifts_registered_map (registered)');
        $this->addSql('CREATE TABLE shift_registration_shifts_roles_map (registration_shift BIGINT NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(registration_shift, role))');
        $this->addSql('CREATE INDEX IDX_1C469ACF2AA19BAD ON shift_registration_shifts_roles_map (registration_shift)');
        $this->addSql('CREATE INDEX IDX_1C469ACF57698A6A ON shift_registration_shifts_roles_map (role)');
        $this->addSql('CREATE TABLE shift_registration_shifts_registered (id BIGINT NOT NULL, person BIGINT DEFAULT NULL, signup_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, username VARCHAR(50) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, ticket_code VARCHAR(100) DEFAULT NULL, member BOOLEAN DEFAULT \'false\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C68EE20C34DCD176 ON shift_registration_shifts_registered (person)');
        $this->addSql('ALTER TABLE registration_shift_users_people_academic_years_map ADD CONSTRAINT FK_DE7A1E7234DCD176 FOREIGN KEY (person) REFERENCES users_people (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE registration_shift_users_people_academic_years_map ADD CONSTRAINT FK_DE7A1E72275AE721 FOREIGN KEY (academic_year) REFERENCES general_academic_years (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shift_registration_shifts ADD CONSTRAINT FK_D32A58FE5A8B0903 FOREIGN KEY (creation_person) REFERENCES users_people (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shift_registration_shifts ADD CONSTRAINT FK_D32A58FE275AE721 FOREIGN KEY (academic_year) REFERENCES general_academic_years (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shift_registration_shifts ADD CONSTRAINT FK_D32A58FEDCBB0C53 FOREIGN KEY (unit) REFERENCES general_organizations_units (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shift_registration_shifts ADD CONSTRAINT FK_D32A58FE3BAE0AA7 FOREIGN KEY (event) REFERENCES nodes_events (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shift_registration_shifts ADD CONSTRAINT FK_D32A58FE5E9E89CB FOREIGN KEY (location) REFERENCES general_locations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shift_registration_shifts_registered_map ADD CONSTRAINT FK_E971DA77897044DB FOREIGN KEY (registrations_shift) REFERENCES shift_registration_shifts (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shift_registration_shifts_registered_map ADD CONSTRAINT FK_E971DA774BFEE160 FOREIGN KEY (registered) REFERENCES shift_registration_shifts_registered (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shift_registration_shifts_roles_map ADD CONSTRAINT FK_1C469ACF2AA19BAD FOREIGN KEY (registration_shift) REFERENCES shift_registration_shifts (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shift_registration_shifts_roles_map ADD CONSTRAINT FK_1C469ACF57698A6A FOREIGN KEY (role) REFERENCES acl_roles (name) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shift_registration_shifts_registered ADD CONSTRAINT FK_C68EE20C34DCD176 FOREIGN KEY (person) REFERENCES users_people (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param  \Doctrine\DBAL\Schema\Schema $schema
     * @return void
     */
    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
