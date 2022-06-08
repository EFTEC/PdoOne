<?php
/** @noinspection AccessModifierPresentedInspection
 * @noinspection PhpUnusedAliasInspection
 * @noinspection UnknownInspectionInspection
 * @noinspection PhpUnused
 * @noinspection ReturnTypeCanBeDeclaredInspection
 */
namespace eftec\examples\clitest\repo2;

use Exception;

/**
 * Class CustomerRepo Copyright (c) Jorge Castro C. (https://github.com/EFTEC/PdoOne)<br>
 * <ul>
 * <li>customer_id int </li>
 * <li>store_id int </li>
 * <li>first_name string </li>
 * <li>last_name string </li>
 * <li>email string </li>
 * <li>address_id int </li>
 * <li>active int </li>
 * <li>create_date datetime </li>
 * <li>last_update timestamp </li>
 * <li>_address_id MANYTOONE (AddresRepoModel)</li>
 * <li>_store_id MANYTOONE (StoreRepoModel)</li>
 * <li>_payment ONETOMANY (PaymentRepoModel)</li>
 * <li>_rental ONETOMANY (RentalRepoModel)</li>
 * </ul>
 * Generated by PdoOne Version 2.27 Date generated Mon, 28 Feb 2022 00:10:18 -0300.<br>
 * <b>YOU CAN EDIT THIS CODE</b>. It is not replaced by the generation of the code, unless it is indicated<br>
 * <pre>
 * $code=$pdoOne->generateCodeClassRepo(''customer'',''eftec\examples\clitest\repo2'','array('actor'=>'ActorRepo','actor2'=>'Actor2Repo','address'=>'AddresRepo','category'=>'CategoryRepo','city'=>'CityRepo','country'=>'CountryRepo','customer'=>'CustomerRepo','dummyt'=>'DummytRepo','dummytable'=>'DummytableRepo','film'=>'FilmRepo','film2'=>'Film2Repo','film_actor'=>'FilmActorRepo','film_category'=>'FilmCategoryRepo','film_text'=>'FilmTextRepo','fum_jobs'=>'FumJobRepo','fum_logs'=>'FumLogRepo','inventory'=>'InventoryRepo','language'=>'LanguageRepo','mysec_table'=>'MysecTableRepo','payment'=>'PaymentRepo','product'=>'ProductRepo','producttype'=>'ProducttypeRepo','producttype_auto'=>'ProducttypeAutoRepo','rental'=>'RentalRepo','staff'=>'StaffRepo','store'=>'StoreRepo','tablachild'=>'TablachildRepo','tablagrandchild'=>'TablagrandchildRepo','tablaparent'=>'TablaparentRepo','tabletest'=>'TabletestRepo','test_products'=>'TestProductRepo','typetable'=>'TypetableRepo',)','''','array(0=>array(0=>'customer_id',1=>'int',2=>NULL,),1=>array(0=>'store_id',1=>'int',2=>NULL,),2=>array(0=>'first_name',1=>'string',2=>NULL,),3=>array(0=>'last_name',1=>'string',2=>NULL,),4=>array(0=>'email',1=>'string',2=>NULL,),5=>array(0=>'address_id',1=>'int',2=>NULL,),6=>array(0=>'active',1=>'int',2=>NULL,),7=>array(0=>'create_date',1=>'datetime',2=>NULL,),8=>array(0=>'last_update',1=>'timestamp',2=>NULL,),9=>array(0=>'_address_id',1=>'MANYTOONE',2=>'AddresRepoModel',),10=>array(0=>'_store_id',1=>'MANYTOONE',2=>'StoreRepoModel',),11=>array(0=>'_payment',1=>'ONETOMANY',2=>'PaymentRepoModel',),12=>array(0=>'_rental',1=>'ONETOMANY',2=>'RentalRepoModel',),)');
 * </pre>
 * @see AddresRepoModel
 * @see StoreRepoModel
 * @see PaymentRepoModel
 * @see RentalRepoModel
 */
class CustomerRepo extends AbstractCustomerRepo
{
    const ME=__CLASS__;
    


}
