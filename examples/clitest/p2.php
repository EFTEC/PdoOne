<?php http_response_code(404); die(1); // eftec/CliOne configuration file ?>
{
    "help": false,
    "first": "generate",
    "definition": "",
    "databasetype": "mysql",
    "server": "127.0.0.1",
    "user": "root",
    "password": "abc.123",
    "database": "sakila",
    "classdirectory": "repo2",
    "classnamespace": "eftec\\examples\\clitest\\repo2",
    "namespace": null,
    "savegen": "yes",
    "tables": null,
    "tablescolumns": "",
    "tablecommand": "",
    "convertionselected": null,
    "convertionnewvalue": null,
    "newclassname": null,
    "overridegenerate": null,
    "tablexclass": {
        "actor": "ActorRepo",
        "cities": "CityRepo",
        "failed_jobs": "FailedJobRepo",
        "migrations": "MigrationRepo",
        "mytable": "MytableRepo",
        "mytokens": "MytokenRepo",
        "password_resets": "PasswordResetRepo",
        "personal_access_tokens": "PersonalAccessTokenRepo",
        "sessions": "SessionRepo",
        "users": "UserRepo"
    },
    "conversion": {
        "bigint": null,
        "blob": null,
        "char": null,
        "date": null,
        "datetime": null,
        "decimal": null,
        "double": null,
        "enum": null,
        "float": null,
        "geometry": null,
        "int": null,
        "json": null,
        "longblob": null,
        "mediumint": null,
        "mediumtext": null,
        "set": null,
        "smallint": null,
        "text": null,
        "time": null,
        "timestamp": null,
        "tinyint": null,
        "varbinary": null,
        "varchar": null,
        "year": null
    },
    "alias": [],
    "extracolumn": {
        "actor": [],
        "cities": [],
        "failed_jobs": [],
        "migrations": [],
        "mytable": [],
        "mytokens": [],
        "password_resets": [],
        "personal_access_tokens": [],
        "sessions": [],
        "users": []
    },
    "removecolumn": [],
    "columnsTable": {
        "actor": {
            "created_at": null,
            "id": null,
            "name": null,
            "updated_at": null
        },
        "cities": {
            "created_at": null,
            "id": null,
            "updated_at": null
        },
        "failed_jobs": {
            "connection": null,
            "exception": null,
            "failed_at": null,
            "id": null,
            "payload": null,
            "queue": null,
            "uuid": null
        },
        "migrations": {
            "batch": null,
            "id": null,
            "migration": null
        },
        "mytable": {
            "myenable": null,
            "mypassword": null,
            "myuser": null
        },
        "mytokens": {
            "KEYT": null,
            "TIMESTAMP": null,
            "VALUE": null
        },
        "password_resets": {
            "created_at": null,
            "email": null,
            "token": null
        },
        "personal_access_tokens": {
            "abilities": null,
            "created_at": null,
            "id": null,
            "last_used_at": null,
            "name": null,
            "token": null,
            "tokenable_id": null,
            "tokenable_type": null,
            "updated_at": null
        },
        "sessions": {
            "id": null,
            "ip_address": null,
            "last_activity": null,
            "payload": null,
            "user_agent": null,
            "user_id": null
        },
        "users": {
            "created_at": null,
            "current_team_id": null,
            "email": null,
            "email_verified_at": null,
            "id": null,
            "name": null,
            "password": null,
            "profile_photo_path": null,
            "remember_token": null,
            "two_factor_recovery_codes": null,
            "two_factor_secret": null,
            "updated_at": null
        }
    },
    "columnsAlias": {
        "actor": {
            "created_at": "CreatedAt",
            "id": "Id",
            "name": "Name",
            "updated_at": "UpdateAt"
        },
        "cities": {
            "created_at": "created_at",
            "id": "id",
            "updated_at": "updated_at"
        },
        "failed_jobs": {
            "connection": "connection",
            "exception": "exception",
            "failed_at": "failed_at",
            "id": "id",
            "payload": "payload",
            "queue": "queue",
            "uuid": "uuid"
        },
        "migrations": {
            "batch": "batch",
            "id": "id",
            "migration": "migration"
        },
        "mytable": {
            "myenable": "myenable",
            "mypassword": "mypassword",
            "myuser": "myuser"
        },
        "mytokens": {
            "KEYT": "KEYT",
            "TIMESTAMP": "TIMESTAMP",
            "VALUE": "VALUE"
        },
        "password_resets": {
            "created_at": "created_at",
            "email": "email",
            "token": "token"
        },
        "personal_access_tokens": {
            "abilities": "abilities",
            "created_at": "created_at",
            "id": "id",
            "last_used_at": "last_used_at",
            "name": "name",
            "token": "token",
            "tokenable_id": "tokenable_id",
            "tokenable_type": "tokenable_type",
            "updated_at": "updated_at"
        },
        "sessions": {
            "id": "id",
            "ip_address": "ip_address",
            "last_activity": "last_activity",
            "payload": "payload",
            "user_agent": "user_agent",
            "user_id": "user_id"
        },
        "users": {
            "created_at": "created_at",
            "current_team_id": "current_team_id",
            "email": "email",
            "email_verified_at": "email_verified_at",
            "id": "id",
            "name": "name",
            "password": "password",
            "profile_photo_path": "profile_photo_path",
            "remember_token": "remember_token",
            "two_factor_recovery_codes": "two_factor_recovery_codes",
            "two_factor_secret": "two_factor_secret",
            "updated_at": "updated_at"
        }
    }
}