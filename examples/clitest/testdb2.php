<?php http_response_code(404); die(1); // eftec/CliOne configuration file ?>
{
    "help": false,
    "first": "generate",
    "definition": "",
    "databasetype": "mysql",
    "server": "127.0.0.1",
    "user": "root",
    "password": "abc.123",
    "database": "testdb2",
    "classdirectory": "testdb2",
    "classpostfix": "Repo",
    "classnamespace": "eftec\\examples\\clitest\\testdb2",
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
        "cities": "City",
        "categories": "Category",
        "customers": "Customer",
        "customerxcategories": "CustomerXCategory",
        "invoicedetails": "InvoiceDetail",
        "invoices": "Invoice",
        "products": "Product",
        "users": "User",
        "invoicetypes": "Invoicetyp",
        "invoicextypes": "Invoicextyp"
    },
    "conversion": {
        "bigint": null,
        "blob": null,
        "char": null,
        "date": "datetime3",
        "datetime": "datetime3",
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
        "cities": [],
        "categories": [],
        "customers": [],
        "customerxcategories": [],
        "invoicedetails": [],
        "invoices": [],
        "products": [],
        "users": [],
        "invoicetypes": [],
        "invoicextypes": []
    },
    "removecolumn": [],
    "columnsTable": {
        "categories": {
            "IdCategory": null,
            "Name": null,
            "_customerxcategories": "ONETOMANY"
        },
        "customers": {
            "City": null,
            "Email": null,
            "IdCustomer": null,
            "Name": null,
            "_City": "MANYTOONE",
            "_customerxcategories": "MANYTOMANY",
            "_invoices": "PARENT",
            "Flag": null
        },
        "customerxcategories": {
            "Category": null,
            "Customer": null,
            "_Category": "MANYTOONE",
            "_Customer": "ONETOONE"
        },
        "invoicedetails": {
            "IdInvoiceDetail": null,
            "Invoice": null,
            "Product": null,
            "Quantity": null,
            "_Invoice": "PARENT",
            "_Product": "MANYTOONE",
            "Flag": null
        },
        "invoices": {
            "Customer": null,
            "Date": null,
            "IdInvoice": null,
            "Total": null,
            "_Customer": "MANYTOONE",
            "_invoicedetails": "ONETOMANY",
            "_invoicextypes": "MANYTOMANY",
            "Flag": null
        },
        "products": {
            "City": null,
            "IdProducts": null,
            "_City": "MANYTOONE",
            "_invoicedetails": "PARENT",
            "Name": null,
            "unitPrice": null,
            "Flag": null
        },
        "cities": {
            "IdCity": null,
            "Name": null,
            "_customers": "ONETOMANY",
            "_products": "ONETOMANY"
        },
        "users": {
            "fullname": null,
            "iduser": null,
            "pwd": null,
            "user": null
        },
        "invoicetypes": {
            "IdInvoiceType": null,
            "NameType": null,
            "_invoicextypes": "ONETOMANY",
            "Flag": null
        },
        "invoicextypes": {
            "IdInvoice": null,
            "IdInvoiceType": null,
            "_IdInvoice": "ONETOONE",
            "_IdInvoiceType": "MANYTOONE"
        }
    },
    "columnsAlias": {
        "categories": {
            "IdCategory": "NumCategory",
            "Name": "Name",
            "_customerxcategories": "_customerxcategories"
        },
        "cities": {
            "IdCity": "NumCity",
            "Name": "Name",
            "_customers": "_clientes",
            "_products": "_products"
        },
        "customerxcategories": {
            "Category": "Category",
            "Customer": "Customer",
            "_Category": "_Category",
            "_Customer": "_Customer"
        },
        "invoicedetails": {
            "IdInvoiceDetail": "NumInvoiceDetail",
            "Invoice": "Invoice",
            "Product": "Product",
            "Quantity": "Quantity",
            "_Invoice": "_Invoice",
            "_Product": "_Product",
            "Flag": "Flag"
        },
        "invoices": {
            "Customer": "Customer",
            "Date": "Date",
            "IdInvoice": "NumInvoice",
            "Total": "Total",
            "_Customer": "_InvCustomer",
            "_invoicedetails": "_Details",
            "_invoicextypes": "_Types",
            "Flag": "FlagAlias"
        },
        "products": {
            "City": "Ciudad",
            "IdProducts": "Numproduct",
            "Name": "Name",
            "unitPrice": "unitPrice",
            "_City": "_CiudadRef",
            "_invoicedetails": "_invoicedetails",
            "Flag": "Flag"
        },
        "customers": {
            "City": "City",
            "Email": "Email",
            "IdCustomer": "NumCustomer",
            "Name": "Name",
            "_City": "_City",
            "_customerxcategories": "_customerxcategories",
            "_invoices": "_invoices",
            "Flag": "FlagAlias"
        },
        "users": {
            "fullname": "fullname",
            "iduser": "iduser",
            "pwd": "pwd",
            "user": "user"
        },
        "invoicetypes": {
            "IdInvoiceType": "NumInvoiceType",
            "NameType": "NameType",
            "_invoicextypes": "_invoicextypes",
            "Flag": "Flag"
        },
        "invoicextypes": {
            "IdInvoice": "Col1X",
            "IdInvoiceType": "Col2X",
            "_IdInvoice": "_Col1X",
            "_IdInvoiceType": "_Col2X"
        }
    }
}