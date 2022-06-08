<?php http_response_code(404); die(1); // eftec/CliOne configuration file ?>
{
    "help": false,
    "first": "generate",
    "definition": "",
    "databasetype": "mysql",
    "server": "127.0.0.1",
    "user": "root",
    "password": "abc.123",
    "database": "testdb",
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
        "TableCategory": "TablecategoryRepo",
        "TableChild": "TablechildRepo",
        "TableGrandChild": "TablegrandchildRepo",
        "TableGrandChildTag": "TablegrandchildtagRepo",
        "TableParent": "TableParentRepo",
        "TableParentExt": "TableparentextRepo",
        "TableParentxCategory": "TableparentxcategoryRepo"
    },
    "conversion": [],
    "alias": [],
    "extracolumn": {
        "TableCategory": [],
        "TableChild": [],
        "TableGrandChild": [],
        "TableGrandChildTag": [],
        "TableParent": [],
        "TableParentExt": [],
        "TableParentxCategory": []
    },
    "removecolumn": [],
    "columnsTable": {
        "TableCategory": {
            "IdTableCategoryPK": null,
            "Name": null,
            "_TableParentxCategory": "ONETOMANY"
        },
        "TableChild": {
            "idgrandchildFK": null,
            "idtablachildPK": null,
            "NameChild": null,
            "_idgrandchildFK": "MANYTOONE",
            "_TableParent": "ONETOMANY"
        },
        "TableGrandChild": {
            "idgrandchildPK": null,
            "NameGrandChild": null,
            "_TableChild": "ONETOMANY"
        },
        "TableGrandChildTag": {
            "IdgrandchildFK": null,
            "IdTablaGrandChildTagPK": null,
            "Name": null
        },
        "TableParent": {
            "fieldDateTime": null,
            "fielDecimal": null,
            "fieldInt": null,
            "fieldKey": null,
            "fieldUnique": null,
            "fieldVarchar": null,
            "idchild2FK": null,
            "idchildFK": null,
            "idtablaparentPK": null,
            "_idchild2FK": "MANYTOONE",
            "_idchildFK": "MANYTOONE",
            "_TableParentExt": "ONETOONE",
            "_TableParentxCategory": "ONETOMANY"
        },
        "TableParentExt": {
            "fieldExt": null,
            "idtablaparentExtPK": null,
            "_idtablaparentExtPK": "ONETOONE"
        },
        "TableParentxCategory": {
            "idcategoryPKFK": null,
            "idtablaparentPKFK": null,
            "_idcategoryPKFK": "MANYTOONE",
            "_idtablaparentPKFK": "ONETOONE"
        }
    },
    "columnsAlias": {
        "TableCategory": {
            "IdTableCategoryPK": "IdTableCategoryPK",
            "Name": "Name"
        },
        "TableChild": {
            "idgrandchildFK": "idgrandchildFK",
            "idtablachildPK": "idtablachildPK",
            "NameChild": "NameChild"
        },
        "TableGrandChild": {
            "idgrandchildPK": "idgrandchildPK",
            "NameGrandChild": "NameGrandChild"
        },
        "TableGrandChildTag": {
            "IdgrandchildFK": "IdgrandchildFK",
            "IdTablaGrandChildTagPK": "IdTablaGrandChildTagPK",
            "Name": "Name"
        },
        "TableParent": {
            "fieldDateTime": "fieldDateTime",
            "fielDecimal": "fielDecimal",
            "fieldInt": "fieldInt",
            "fieldKey": "FieldKey",
            "fieldUnique": "fieldUnique",
            "fieldVarchar": "FieldText",
            "idchild2FK": "IdChild2Foreign",
            "idchildFK": "idchildFK",
            "idtablaparentPK": "idtablaparentPK"
        },
        "TableParentExt": {
            "fieldExt": "fieldExt",
            "idtablaparentExtPK": "idtablaparentExtPK"
        },
        "TableParentxCategory": {
            "idcategoryPKFK": "idcategoryPKFK",
            "idtablaparentPKFK": "idtablaparentPKFK"
        }
    }
}