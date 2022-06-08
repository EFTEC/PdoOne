<?php http_response_code(404); die(1); // eftec/CliOne configuration file ?>
{
    "help": false,
    "databasetype": "mysql",
    "server": "127.0.0.1",
    "user": "root",
    "password": "abc.123",
    "database": "sakila",
    "classdirectory": "repo2",
    "classnamespace": "eftec\\examples\\clitest\\repo2",
    "input": "",
    "output": "",
    "namespace": "",
    "savegen": null,
    "tables": null,
    "tablescolumns": null,
    "tablecommand": null,
    "convertionselected": null,
    "convertionnewvalue": null,
    "newclassname": null,
    "overridegenerate": "yes",
    "tablexclass": {
        "actor": "ActorRepo",
        "actor2": "Actor2Repo",
        "address": "AddresRepo",
        "category": "CategoryRepo",
        "city": "CityRepo",
        "country": "CountryRepo",
        "customer": "CustomerRepo",
        "dummyt": "DummytRepo",
        "dummytable": "DummytableRepo",
        "film": "FilmRepo",
        "film2": "Film2Repo",
        "film_actor": "FilmActorRepo",
        "film_category": "FilmCategoryRepo",
        "film_text": "FilmTextRepo",
        "fum_jobs": "FumJobRepo",
        "fum_logs": "FumLogRepo",
        "inventory": "InventoryRepo",
        "language": "LanguageRepo",
        "mysec_table": "MysecTableRepo",
        "payment": "PaymentRepo",
        "product": "ProductRepo",
        "producttype": "ProducttypeRepo",
        "producttype_auto": "ProducttypeAutoRepo",
        "rental": "RentalRepo",
        "staff": "StaffRepo",
        "store": "StoreRepo",
        "tablachild": "TablachildRepo",
        "tablagrandchild": "TablagrandchildRepo",
        "tablaparent": "TablaparentRepo",
        "tabletest": "TabletestRepo",
        "test_products": "TestProductRepo",
        "typetable": "TypetableRepo"
    },
    "conversion": [],
    "extracolumn": {
        "actor": [],
        "actor2": [],
        "address": [],
        "category": [],
        "city": [],
        "country": [],
        "customer": [],
        "dummyt": [],
        "dummytable": [],
        "film": [],
        "film2": [],
        "film_actor": [],
        "film_category": [],
        "film_text": [],
        "fum_jobs": [],
        "fum_logs": [],
        "inventory": [],
        "language": [],
        "mysec_table": [],
        "payment": [],
        "product": [],
        "producttype": [],
        "producttype_auto": [],
        "rental": [],
        "staff": [],
        "store": [],
        "tablachild": [],
        "tablagrandchild": [],
        "tablaparent": [],
        "tabletest": [],
        "test_products": [],
        "typetable": []
    },
    "removecolumn": [],
    "columnsTable": {
        "actor": {
            "actor_id": null,
            "first_name": null,
            "last_name": null,
            "last_update": null,
            "_film_actor": "ONETOMANY"
        },
        "actor2": {
            "actor_id": null,
            "first_name": null,
            "last_name": null,
            "last_update": null
        },
        "address": {
            "address": null,
            "address2": null,
            "address_id": null,
            "city_id": null,
            "district": null,
            "last_update": null,
            "phone": null,
            "postal_code": null,
            "_city_id": "MANYTOONE",
            "_customer": "ONETOMANY",
            "_staff": "ONETOMANY",
            "_store": "ONETOMANY"
        },
        "category": {
            "category_id": null,
            "last_update": null,
            "name": null,
            "_film_category": "ONETOMANY"
        },
        "city": {
            "city": null,
            "city_id": null,
            "country_id": null,
            "last_update": null,
            "_country_id": "MANYTOONE",
            "_address": "ONETOMANY",
            "xxxx": "new"
        },
        "country": {
            "country": null,
            "country_id": null,
            "last_update": null,
            "_city": "ONETOMANY"
        },
        "customer": {
            "active": null,
            "address_id": null,
            "create_date": null,
            "customer_id": null,
            "email": null,
            "first_name": null,
            "last_name": null,
            "last_update": null,
            "store_id": null,
            "_address_id": "MANYTOONE",
            "_store_id": "MANYTOONE",
            "_payment": "ONETOMANY",
            "_rental": "ONETOMANY"
        },
        "dummyt": {
            "int1": null,
            "int2": null,
            "int3": null
        },
        "dummytable": {
            "dummytablecol": null,
            "dummytablecol1": null,
            "dummytablecol10": null,
            "dummytablecol11": null,
            "dummytablecol12": null,
            "dummytablecol13": null,
            "dummytablecol14": null,
            "dummytablecol15": null,
            "dummytablecol16": null,
            "dummytablecol17": null,
            "dummytablecol2": null,
            "dummytablecol3": null,
            "dummytablecol4": null,
            "dummytablecol5": null,
            "dummytablecol6": null,
            "dummytablecol7": null,
            "dummytablecol8": null,
            "dummytablecol9": null,
            "iddummytable": null
        },
        "film": {
            "description": null,
            "film_id": null,
            "language_id": null,
            "last_update": null,
            "length": null,
            "original_language_id": null,
            "rating": null,
            "release_year": null,
            "rental_duration": null,
            "rental_rate": null,
            "replacement_cost": null,
            "special_features": null,
            "title": null,
            "_language_id": "MANYTOONE",
            "_original_language_id": "MANYTOONE",
            "_film_actor": "ONETOMANY",
            "_film_category": "ONETOMANY",
            "_inventory": "ONETOMANY"
        },
        "film2": {
            "description": null,
            "film_id": null,
            "language_id": null,
            "last_update": null,
            "length": null,
            "original_language_id": null,
            "rating": null,
            "release_year": null,
            "rental_duration": null,
            "rental_rate": null,
            "replacement_cost": null,
            "special_features": null,
            "title": null,
            "_language_id": "MANYTOONE",
            "_original_language_id": "MANYTOONE"
        },
        "film_actor": {
            "actor_id": null,
            "film_id": null,
            "last_update": null,
            "_actor_id": "ONETOONE",
            "_film_id": "MANYTOONE"
        },
        "film_category": {
            "category_id": null,
            "film_id": null,
            "last_update": null,
            "_category_id": "MANYTOONE",
            "_film_id": "ONETOONE"
        },
        "film_text": {
            "description": null,
            "film_id": null,
            "title": null
        },
        "fum_jobs": {
            "CHIMNEYOPEN": null,
            "CHIMNEYPRESENT": null,
            "CURMEDICION": null,
            "dateend": null,
            "dateexpired": null,
            "dateinit": null,
            "datelastchange": null,
            "DELTATIMEEVACUATION": null,
            "DUMPOPEN": null,
            "FANOPEN": null,
            "idactive": null,
            "IDCHAMBER": null,
            "IDFUMIGATION": null,
            "idjob": null,
            "IDPROCESS": null,
            "idstate": null,
            "INJECTCOUNTER": null,
            "INJECTOPEN": null,
            "MAXTEMP": null,
            "MINTEMP": null,
            "PESO_FINAL": null,
            "PESO_INICIAL": null,
            "PESOACTUAL": null,
            "PESOESPERADO": null,
            "RANGEMINTEMP": null,
            "RANGETEMP": null,
            "START": null,
            "text_job": null,
            "TIMEELAPSED": null,
            "TIMEEND": null,
            "TIMEENDTIMEOUT": null,
            "TIMEEVACUATION": null,
            "TIMEINIT": null,
            "TIMENOFAN": null,
            "TIMEPREEND": null
        },
        "fum_logs": {
            "date": null,
            "description": null,
            "idjob": null,
            "idjoblog": null,
            "idrel": null,
            "type": null
        },
        "inventory": {
            "film_id": null,
            "inventory_id": null,
            "last_update": null,
            "store_id": null,
            "_film_id": "MANYTOONE",
            "_store_id": "MANYTOONE",
            "_rental": "ONETOMANY"
        },
        "language": {
            "language_id": null,
            "last_update": null,
            "name": null,
            "_film": "ONETOMANY",
            "_film2": "ONETOMANY"
        },
        "mysec_table": {
            "id": null,
            "stub": null
        },
        "payment": {
            "amount": null,
            "customer_id": null,
            "last_update": null,
            "payment_date": null,
            "payment_id": null,
            "rental_id": null,
            "staff_id": null,
            "_customer_id": "MANYTOONE",
            "_rental_id": "MANYTOONE",
            "_staff_id": "MANYTOONE"
        },
        "product": {
            "idproduct": null,
            "name": null
        },
        "producttype": {
            "idproducttype": null,
            "name": null,
            "type": null
        },
        "producttype_auto": {
            "idproducttype": null,
            "name": null,
            "type": null
        },
        "rental": {
            "customer_id": null,
            "inventory_id": null,
            "last_update": null,
            "rental_date": null,
            "rental_id": null,
            "return_date": null,
            "staff_id": null,
            "_customer_id": "MANYTOONE",
            "_inventory_id": "MANYTOONE",
            "_staff_id": "MANYTOONE",
            "_payment": "ONETOMANY"
        },
        "staff": {
            "active": null,
            "address_id": null,
            "email": null,
            "first_name": null,
            "last_name": null,
            "last_update": null,
            "password": null,
            "picture": null,
            "staff_id": null,
            "store_id": null,
            "username": null,
            "_address_id": "MANYTOONE",
            "_store_id": "MANYTOONE",
            "_payment": "ONETOMANY",
            "_rental": "ONETOMANY",
            "_store": "ONETOMANY",
            "_tabletest": "ONETOMANY"
        },
        "store": {
            "address_id": null,
            "last_update": null,
            "manager_staff_id": null,
            "store_id": null,
            "_address_id": "MANYTOONE",
            "_manager_staff_id": "MANYTOONE",
            "_customer": "ONETOMANY",
            "_inventory": "ONETOMANY",
            "_staff": "ONETOMANY"
        },
        "tablachild": {
            "idgrandchildFK": null,
            "idtablachild": null,
            "valuechild": null,
            "_idgrandchildFK": "MANYTOONE",
            "_tablaparent": "ONETOMANY"
        },
        "tablagrandchild": {
            "idgrandchild": null,
            "NameGrandChild": null,
            "_tablachild": "ONETOMANY"
        },
        "tablaparent": {
            "field1": null,
            "idchild": null,
            "idchild2": null,
            "idtablaparent": null,
            "_idchild": "MANYTOONE",
            "_idchild2": "MANYTOONE"
        },
        "tabletest": {
            "col1": null,
            "col2": null,
            "col3": null,
            "col4": null,
            "id": null,
            "_col4": "MANYTOONE"
        },
        "test_products": {
            "idProduct": null,
            "name": null
        },
        "typetable": {
            "name": null,
            "type": null
        }
    }
}