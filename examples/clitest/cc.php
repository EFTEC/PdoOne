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
    "classnamespace": "eftec\\examples\\clitest\\testdb2",
    "namespace": null,
    "savegen": null,
    "tables": null,
    "tablescolumns": null,
    "tablecommand": null,
    "convertionselected": null,
    "convertionnewvalue": null,
    "newclassname": null,
    "overridegenerate": "yes",
    "tablexclass": {
        "cities": "CityRepo",
        "categories": "CategoryRepo",
        "customers": "CustomerRepo",
        "customerxcategories": "CustomerXCategoryRepo",
        "invoicedetails": "InvoicedetailRepo",
        "invoices": "InvoiceRepo",
        "products": "ProductRepo"
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
        "cities": [],
        "categories": [],
        "customers": [],
        "customerxcategories": [],
        "invoicedetails": [],
        "invoices": [],
        "products": []
    },
    "removecolumn": [],
    "columnsTable": {
        "categories": {
            "IdCategory": null,
            "Name": null,
            "_customerxcategories": "ONETOMANY"
        },
        "cities": {
            "IdCity": null,
            "Name": null,
            "_customers": "PARENT",
            "_products": "PARENT"
        },
        "customers": {
            "City": null,
            "Email": null,
            "IdCustomer": null,
            "Name": null,
            "_City": "MANYTOONE",
            "_customerxcategories": "MANYTOMANY",
            "_invoices": "PARENT"
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
            "_Product": "MANYTOONE"
        },
        "invoices": {
            "Customer": null,
            "Date": null,
            "IdInvoice": null,
            "Total": null,
            "_Customer": "MANYTOONE",
            "_invoicedetails": "ONETOMANY"
        },
        "products": {
            "City": null,
            "IdProducts": null,
            "Name": null,
            "_City": "MANYTOONE",
            "_invoicedetails": "PARENT"
        }
    },
    "columnsAlias": {
        "categories": {
            "IdCategory": "IdCategory",
            "Name": "Name"
        },
        "cities": {
            "IdCity": "IdCity",
            "Name": "Name"
        },
        "customers": {
            "City": "City",
            "Email": "Email",
            "IdCustomer": "IdCustomer",
            "Name": "Name"
        },
        "customerxcategories": {
            "Category": "Category",
            "Customer": "Customer"
        },
        "invoicedetails": {
            "IdInvoiceDetail": "IdInvoiceDetail",
            "Invoice": "Invoice",
            "Product": "Product",
            "Quantity": "Quantity"
        },
        "invoices": {
            "Customer": "Customer",
            "Date": "Date",
            "IdInvoice": "IdInvoice",
            "Total": "Total"
        },
        "products": {
            "City": "City",
            "IdProducts": "IdProducts",
            "Name": "Name"
        }
    }
}