<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Internal library of functions for module mgm
 *
 * All the mgm specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod_mgm
 * @copyright 2013 Jesús Jaén <jesus.jaen@open-phoenix.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once ($CFG -> dirroot . '/course/lib.php');
require_once ($CFG -> dirroot . '/group/lib.php');
require_once ($CFG -> libdir . '/excellib.class.php');
require_once ($CFG -> libdir . '/odslib.class.php');
require_once($CFG -> dirroot . '/user/profile/lib.php');

defined('MOODLE_INTERNAL') || die();

define('EDICIONES', $CFG -> prefix . 'ediciones');
define('EDICIONES_COURSE', $CFG -> prefix . 'ediciones_course');

define('MGM_CERTIFICATE_NONE', 0);
define('MGM_CERTIFICATE_DRAFT', 1);
define('MGM_CERTIFICATE_VALIDATED', 2);

define('MGM_CRITERIA_PLAZAS', 0);
define('MGM_CRITERIA_OPCION1', 1);
define('MGM_CRITERIA_OPCION2', 2);
define('MGM_CRITERIA_ESPECIALIDAD', 3);
define('MGM_CRITERIA_CC', 4);
define('MGM_CRITERIA_MINGROUP', 5);
define('MGM_CRITERIA_MAXGROUP', 6);
define('MGM_CRITERIA_DEPEND', 7);
define('MGM_CRITERIA_NUMGROUPS', 8);
define('MGM_CRITERIA_COMUNIDAD', 9);
define('MGM_CRITERIA_PAGO', 10);
define('MGM_CRITERIA_ECUADOR', 11);
define('MGM_CRITERIA_DURATION', 12);
define('MGM_CRITERIA_PREVLAB', 13);
define('MGM_CRITERIA_TRAMOS', 14);

define('MGM_ITE_ESPECIALIDADES', 1);
define('MGM_ITE_CENTROS', 2);
define('MGM_ITE_SCALA', 3);
define('MGM_ITE_ROLE', 4);
define('MGM_ITE_REPORT', 5);

define('MGM_DATA_NO_ERROR', 0);
define('MGM_DATA_CC_ERROR', 1);
define('MGM_DATA_CC_ERROR_PRIVATE', 2);
define('MGM_DATA_DNI_ERROR', 3);
define('MGM_DATA_DNI_INVALID', 4);

define('MGM_PUBLIC_CENTER', 0);
define('MGM_MIXIN_CENTER', 1);
define('MGM_PRIVATE_CENTER', 2);

define('MGM_ECUADOR_DEFAULT', 0);

define('MGM_STATE_NO_ERROR', 0);// SIN ERROR
define('MGM_STATE_PA_ERROR', 1);//edicion no activa
define('MGM_STATE_PM_ERROR', 2);//preinscripcion multiple
define('MGM_STATE_BN_ERROR', 3);//una nueva edicion solo puede estar  en estado borrador
define('MGM_STATE_MN_ERROR', 4);//una edición solo puede estar en estado matriculacion cuando ha terminado el plazo de inscripcion.
define('MGM_STATE_BI_ERROR', 5);//no se puede modificar una edicion finalizada.
define('MGM_STATE_FI_ERROR', 6);//una edición que no esta en estado gestion no puede pasar a estado finalizada.
define('MGM_STATE_FC_ERROR', 7);//una edición tiene que estar certificada para pasar a estado finalizada.
define('MGM_STATE_FP_ERROR', 8);//una edición tienen que estar pagada para pasar a estado finalizada.
define('MGM_STATE_MA_ERROR', 9);//solo una edicion activa puede estar en matriculacion
define('MGM_STATE_MM_ERROR', 10);//solo puede existir una edcion en estado matriculacion.
define('MGM_STATE_CF_ERROR', 11);//Una edicion en curso tiene que estar entre las fechas de inicio y fin de sus cursos.
define('MGM_STATE_GE_ERROR', 12);//Todos los cursos tienen que haber finalizado en el estado gestión.



global $NIVELES_EDUCATIVOS, $CUERPOS_DOCENTES, $PAISES, $PROVINCIAS, $COMUNIDADES, $MEC_CODES, $MGM_ITE_CUERPOS_ESPECS;

$CODIGOS_AGRUPACION = array("0" => "0 Agrupación General", "1" => "1 Agrupación de Cursos", "2" => "2 Investigación Nacional", "3" => "3 Proyectos de Formación", "4" => "4 Comenius", "5" => "5 Lingua", "6" => "6 Arion", "7" => "7 Petra I y II", "8" => "8 Leonardo Da Vinci", "9" => "9 Música y Danza", "10" => "10 Enseñanza de Idiomas", "11" => "11 Titulaciones de Primer Ciclo", "12" => "12 Titulaciones de Segundo Ciclo", "13" => "13 Titulaciones de Tercer Ciclo", "14" => "14 Investigación Provincial", "15" => "15 Investigación Carácter Distinto", "16" => "16 Proyecto Innovación", "17" => "17 Minerva", "18" => "18 Medidas de acompañamiento", "19" => "19 Grundtvig", "20" => "20 Acciones conjuntas", "25" => "25 En Red");

$MODALIDADES = array("10" => "10 Curso", "20" => "20 Grupos de Trabajo", "30" => "30 Seminarios", "A0" => "A0 Curso a distancia");

$PROVINCIAS = array(10 => "10 ALAVA", 20 => "20 ALBACETE", 30 => "30 ALICANTE", 40 => "40 ALMERIA", 50 => "50 AVILA", 60 => "60 BADAJOZ", 70 => "70 ILLES BALEARS", 80 => "80 BARCELONA", 90 => "90 BURGOS", 100 => "100 CACERES", 110 => "110 CADIZ", 120 => "120 CASTELLON", 130 => "130 CIUDAD REAL", 140 => "140 CORDOBA", 150 => "150 A CORUÑA", 160 => "160 CUENCA", 170 => "170 GIRONA", 180 => "180 GRANADA", 190 => "190 GUADALAJARA", 200 => "200 GUIPUZCOA", 210 => "210 HUELVA", 220 => "220 HUESCA", 230 => "230 JAEN", 240 => "240 LEON", 250 => "250 LLEIDA", 260 => "260 LA RIOJA", 270 => "270 LUGO", 280 => "280 MADRID", 281 => "281 MADRID-NORTE", 282 => "282 MADRID-SUR", 283 => "283 MADRID-ESTE", 284 => "284 MADRID-OESTE", 285 => "285 MADRID-CENTRO", 290 => "290 MALAGA", 300 => "300 MURCIA", 310 => "310 NAVARRA", 320 => "320 OURENSE", 330 => "330 ASTURIAS", 340 => "340 PALENCIA", 350 => "350 LAS PALMAS DE GRAN CANARIA", 360 => "360 PONTEVEDRA", 370 => "370 SALAMANCA", 380 => "380 SANTA CRUZ DE TENERIFE", 390 => "390 CANTABRIA", 400 => "400 SEGOVIA", 410 => "410 SEVILLA", 420 => "420 SORIA", 430 => "430 TARRAGONA", 440 => "440 TERUEL", 450 => "450 TOLEDO", 460 => "460 VALENCIA", 470 => "470 VALLADOLID", 480 => "480 VIZCAYA", 490 => "490 ZAMORA", 500 => "500 ZARAGOZA", 510 => "510 CEUTA", 520 => "520 MELILLA", 600 => "600 EXTRANJERO");

$PAISES = array("004" => "AFGANISTAN",  "008" => "ALBANIA",  "276" => "ALEMANIA",  "020" => "ANDORRA",  "024" => "ANGOLA",  "660" => "ANGUILLA",  "028" => "ANTIGUA Y BARBUDA",  "530" => "ANTILLAS HOLANDESAS",  "682" => "ARABIA SAUDITA",  "012" => "ARGELIA",  "032" => "ARGENTINA",  "051" => "ARMENIA",  "533" => "ARUBA",  "036" => "AUSTRALIA",  "040" => "AUSTRIA",  "031" => "AZERBAIYAN",  "044" => "BAHAMAS",  "048" => "BAHREIN",  "050" => "BANGLADESH",  "052" => "BARBADOS",  "056" => "BÉLGICA",  "084" => "BELICE",  "204" => "BENIN",  "060" => "BERMUDAS",  "064" => "BHUTAN",  "112" => "BIELORRUSIA",  "068" => "BOLIVIA",  "070" => "BOSNIA HERZEGOVINA",  "072" => "BOTSWANA",  "074" => "BOUVET,   ISLA",  "076" => "BRASIL",  "096" => "BRUNEI DARUSSALAM",  "100" => "BULGARIA",  "854" => "BURKINA FASSO",  "108" => "BURUNDI",  "132" => "CABO VERDE",  "136" => "CAIMAN,   ISLAS",  "116" => "CAMBOYA",  "120" => "CAMERUN",  "124" => "CANADA",  "148" => "CHAD",  "900" => "CHECOSLOVAQUIA",  "152" => "CHILE",  "156" => "CHINA",  "196" => "CHIPRE",  "170" => "COLOMBIA",  "174" => "COMORES",  "178" => "CONGO",  "184" => "COOK,   ISLAS",  "384" => "COSTA DE MARFIL",  "188" => "COSTA RICA",  "191" => "CROACIA",  "192" => "CUBA",  "208" => "DINAMARCA",  "262" => "DJIBOUTI",  "212" => "DOMINICA",  "218" => "ECUADOR",  "818" => "EGIPTO",  "222" => "EL SALVADOR",  "784" => "EMIRATOS ARABES UNIDOS",  "232" => "ERITREA",  "703" => "ESLOVAQUIA",  "705" => "ESLOVENIA",  "724" => "ESPAÑA",  "840" => "ESTADOS UNIDOS DE AMERICA",  "233" => "ESTONIA",  "231" => "ETIOPIA",  "234" => "FEROE,   ISLAS",  "242" => "FIJI",  "608" => "FILIPINAS",  "246" => "FINLANDIA",  "250" => "FRANCIA",  "266" => "GABON",  "270" => "GAMBIA",  "268" => "GEORGIA",  "288" => "GHANA",  "292" => "GIBRALTAR",  "308" => "GRANADA",  "300" => "GRECIA",  "304" => "GROENLANDIA",  "312" => "GUADALUPE",  "316" => "GUAM",  "320" => "GUATEMALA",  "254" => "GUAYANA FRANCESA",  "324" => "GUINEA",  "226" => "GUINEA ECUATORIAL",  "624" => "GUINEA-BISSAU",  "328" => "GUYANA",  "332" => "HAITI",  "334" => "HEARD AND MCDONALD,   ISLAS",  "528" => "HOLANDA",  "340" => "HONDURAS",  "348" => "HUNGRIA",  "356" => "INDIA",  "360" => "INDONESIA",  "364" => "IRAN",  "368" => "IRAQ",  "372" => "IRLANDA",  "352" => "ISLANDIA",  "580" => "ISLAS MARIANA",  "584" => "ISLAS MARSHALL",  "090" => "ISLAS SALOMON",  "376" => "ISRAEL",  "380" => "ITALIA",  "388" => "JAMAICA",  "392" => "JAPON",  "400" => "JORDANIA",  "398" => "KAZAJSTAN",  "404" => "KENIA",  "417" => "KIRGUISTAN",  "296" => "KIRIBATI",  "414" => "KUWAIT",  "418" => "LAOS",  "426" => "LESOTHO",  "428" => "LETONIA",  "422" => "LIBANO",  "430" => "LIBERIA",  "434" => "LIBIA",  "438" => "LIECHTENSTEIN",  "440" => "LITUANIA",  "442" => "LUXEMBURGO",  "807" => "MACEDONIA",  "450" => "MADAGASCAR",  "458" => "MALASIA",  "454" => "MALAWI",  "462" => "MALDIVAS",  "466" => "MALI",  "470" => "MALTA",  "238" => "MALVINAS,   ISLAS",  "504" => "MARRUECOS",  "480" => "MAURICIO",  "478" => "MAURITANIA",  "175" => "MAYOTTE",  "484" => "MEXICO",  "583" => "MICRONESIA",  "498" => "MOLDAVIA",  "492" => "MONACO",  "496" => "MONGOLIA",  "499" => "MONTENEGRO",  "508" => "MOZAMBIQUE",  "104" => "MYANMAR",  "516" => "NAMIBIA",  "520" => "NAURU",  "524" => "NEPAL",  "558" => "NICARAGUA",  "562" => "NIGER",  "566" => "NIGERIA",  "570" => "NIUE",  "574" => "NORFOLK,   ISLA",  "578" => "NORUEGA",  "540" => "NUEVA CALEDONIA",  "554" => "NUEVA ZELANDA",  "512" => "OMAN",  "586" => "PAKISTAN",  "585" => "PALAU",  "591" => "PANAMA",  "598" => "PAPUA NUEVA GUINEA",  "600" => "PARAGUAY",  "604" => "PERU",  "612" => "PITCAIRN,  ISLA",  "616" => "POLONIA",  "620" => "PORTUGAL",  "630" => "PUERTO RICO",  "634" => "QATAR",  "826" => "REINO UNIDO",  "140" => "REPUBLICA CENTROAFRICANA",  "203" => "REPUBLICA CHECA",  "410" => "REPUBLICA DE COREA",  "180" => "REPUBLICA DEMOCRATICA DEL CONGO",  "214" => "REPUBLICA DOMINICANA",  "408" => "REPUBLICA POPULAR DEMOCRATICA DE COREA",  "710" => "REPUBLICA SUDAFRICANA",  "638" => "REUNION",  "646" => "RUANDA",  "642" => "RUMANIA",  "643" => "RUSIA",  "882" => "SAMOA",  "016" => "SAMOA NORTEAMERICANA",  "659" => "SAN CRISTOBAL Y NIEVES",  "674" => "SAN MARINO",  "666" => "SAN PEDRO Y MIQUELON",  "670" => "SAN VICENTE Y LAS GRANADINAS",  "654" => "SANTA ELENA",  "662" => "SANTA LUCIA",  "678" => "SANTO TOME Y PRINCIPE",  "686" => "SENEGAL",  "688" => "SERBIA",  "690" => "SEYCHELLES",  "694" => "SIERRA LEONA",  "702" => "SINGAPUR",  "760" => "SIRIA",  "706" => "SOMALIA",  "144" => "SRI LANKA",  "736" => "SUDAN",  "752" => "SUECIA",  "756" => "SUIZA",  "740" => "SURINAM",  "748" => "SWAZILANDIA",  "764" => "TAILANDIA",  "158" => "TAIWAN",  "834" => "TANZANIA",  "762" => "TAYIKISTAN",  "626" => "TIMOR",  "768" => "TOGO",  "772" => "TOKELAU",  "776" => "TONGA",  "780" => "TRINIDAD Y TOBAGO",  "788" => "TUNEZ",  "795" => "TURKMENISTAN",  "792" => "TURQUIA",  "798" => "TUVALU",  "804" => "UCRANIA",  "800" => "UGANDA",  "858" => "URUGUAY",  "860" => "UZBEKISTAN",  "548" => "VANUATU",  "336" => "VATICANO",  "862" => "VENEZUELA",  "704" => "VIETNAM",  "887" => "YEMEN",  "894" => "ZAMBIA",  "716" => "ZIMBABWE");

$MATERIAS = array("0001" => "0001 MATERIA DE MIGRACIÓN", "0101" => "0101 TEOR. Y ORG. CURRICULAR ASP. GENER.", "0102" => "0102 TEORIA DEL CURRICULO", "0103" => "0103 DISEÑO GRAL. DEL CURRICULO", "0104" => "0104 PROYECTO EDUCATIVO DE CENTRO/ZONA", "0105" => "0105 PROYECTO CURRICULAR", "0106" => "0106 PROGRAMACION DE AULA", "0201" => "0201 ID. Y AUT. PERSONAL. ASP. GENERALES", "0202" => "0202 EL CUERPO Y LA PROPIA IMAGEN", "0203" => "0203 JUEGO", "0204" => "0204 LA ACTIVIDAD Y LA VIDA COTIDIANA", "0205" => "0205 EL CUIDADO DE UNO MISMO", "0301" => "0301 MEDIO FISICO Y SOCIAL. ASP. GENER.", "0302" => "0302 LOS PRIMEROS GRUPOS SOCIALES", "0303" => "0303 LOS ANIMALES Y LAS PLANTAS", "0401" => "0401 COMUN. Y REPRES. ASPECTOS GENERALES", "0402" => "0402 LENGUAJE ORAL", "0403" => "0403 APROXIMACION AL LENGUAJE ESCRITO", "0404" => "0404 EXPRESION PLASTICA", "0405" => "0405 EXPRESION MUSICAL", "0406" => "0406 RELACIONES MEDIDA Y REPRESENTACION", "0501" => "0501 CONOCIM DEL MEDIO. ASPECTOS GENER.", "0502" => "0502 CONOCIMIENTO DEL CUERPO Y SALUD", "0503" => "0503 MEDIO SOCIAL", "0504" => "0504 EL PASO DEL TIEMPO", "0505" => "0505 CULTURA Y TECNOLOGIA", "0601" => "0601 ED. ARTISTICA. ASPECTOS GENERALES", "0602" => "0602 EXPRESION PLASTICA Y VISUAL", "0603" => "0603 MUSICA", "0604" => "0604 DRAMATIZACION", "0605" => "0605 ARTES APLICADAS", "0701" => "0701 ED. FISICA. ASPECTOS GENERALES", "0702" => "0702 CONDICION FISICA", "0703" => "0703 JUEGOS", "0704" => "0704 DEPORTES", "0801" => "0801 LENG. Y LIT. ASPECTOS GENERALES", "0802" => "0802 LENGUA ESPAÑOLA", "0803" => "0803 LITERATURA ESPAÑOLA", "0804" => "0804 OTRAS LENGUAS DEL ESTADO", "0805" => "0805 LITERATURA UNIVERSAL", "0901" => "0901 LENG. Y CULT. CLASICAS. ASP. GENER.", "0902" => "0902 LATIN", "0903" => "0903 GRIEGO", "1001" => "1001 LENGUAS EXTR. ASPECTOS GENERALES", "1002" => "1002 INGLES", "1003" => "1003 FRANCES", "1004" => "1004 ALEMAN", "1005" => "1005 ITALIANO", "1006" => "1006 OTROS IDIOMAS", "1101" => "1101 MATEMATICAS. ASPECTOS GENERALES", "1102" => "1102 NUMEROS", "1103" => "1103 ALGEBRA", "1104" => "1104 GEOMETRIA", "1105" => "1105 ANALISIS", "1106" => "1106 ESTADISTICA Y PROBABILIDAD", "1107" => "1107 ASTRONOMIA", "1201" => "1201 CIENC. DE LA NAT. ASPECTOS GENER.", "1202" => "1202 FISICA", "1203" => "1203 QUIMICA", "1204" => "1204 BIOLOGIA", "1205" => "1205 GEOLOGIA", "1206" => "1206 CIENCIAS MEDIOAMBIENTALES", "1301" => "1301 CIENC. SOCIALES. ASPECTOS GENERALES", "1302" => "1302 GEOGRAFIA", "1303" => "1303 HISTORIA", "1304" => "1304 HISTORIA DEL ARTE", "1305" => "1305 FILOSOFIA", "1306" => "1306 ECONOMIA", "1307" => "1307 PSICOLOGIA", "1308" => "1308 SOCIOLOGIA", "1401" => "1401 ED. RELIGIOSA. ASPECTOS GENERALES", "1501" => "1501 TECNOLOGIA. ASPECTOS GENERALES", "1502" => "1502 PROCESOS CREACION Y DESARR. PRODUC.", "1503" => "1503 REPR. Y COMUNICACION PROCESOS TECN.", "1504" => "1504 MATERIALES Y TECNICAS CONSTRUCTIVAS", "1505" => "1505 CONOCIMIENTOS TECNOLOGICOS ESPECIA.", "1506" => "1506 TECNOLOGIA", "1601" => "1601 FORM. PROFESIONAL. ASPECTOS GENER.", "1602" => "1602 ORIENTACION PROFESIONAL", "1603" => "1603 TRANSICION A LA VIDA ACTIVA", "1604" => "1604 FORMACION EN CENTROS DE TRABAJO", "1605" => "1605 GARANTIA SOCIAL", "1606" => "1606 DESARROLLO LOCAL Y REGIONAL", "1607" => "1607 FORMACION CONCERTADA", "1701" => "1701 ACTIVIDADES SOCIOCULTURALES", "1702" => "1702 ADMINISTRACION DE EMPRESAS", "1703" => "1703 ASESORIA DE CONSUMO", "1704" => "1704 AUXILIAR ADMINISTRACION Y GESTION", "1705" => "1705 AUXILIAR DE COMERCIO INTERIOR", "1706" => "1706 BIBLIOTECONOMIA", "1707" => "1707 COMERCIO EXTERIOR", "1708" => "1708 PROGRAMADOR DE GESTION", "1709" => "1709 SECRETARIO EJECUTIVO MULTILINGUE", "1801" => "1801 A. FORESTALES Y CONSERV. NATURALEZA", "1802" => "1802 EXPLOTACIONES AGROPECUARIAS", "1803" => "1803 FITOPATOLOGIA", "1804" => "1804 FRUTICULTURA", "1805" => "1805 HORTICULTURA", "1806" => "1806 HORTOFRUTICULTURA", "1807" => "1807 INSTALACION Y MTO. DE JARDINES", "1808" => "1808 JARDINERO PRODUCTOR DE PLANTAS", "1901" => "1901 FOTOCOMPOSICION", "1902" => "1902 REPRODUCCION FOTOMECANICA", "2001" => "2001 AUTOMOCION", "2002" => "2002 CARROCERIA", "2101" => "2101 ACABADOS DE CONSTRUCCION", "2102" => "2102 ALBAÑILERIA", "2103" => "2103 CUBRICION DE EDIFICIOS", "2104" => "2104 HORMIGON", "2201" => "2201 DELINEACION INDUSTRIAL", "2301" => "2301 INST. Y MTO. EQUIPOS FRIO Y CALOR", "2302" => "2302 INST./MANTENEDOR ELECTRICO", "2303" => "2303 INST. TERMICAS AUX. DE PROCESO", "2304" => "2304 MTO. DE INST. SERVICIO AUXILIARES", "2305" => "2305 SISTEMAS AUTOMATICOS Y PROGRAMABLES", "2401" => "2401 COCINA", "2402" => "2402 RECEPCION", "2501" => "2501 MTO. Y OPER. TEC. EQUIPOS DE R.T.V.", "2502" => "2502 OPERACIONES DE IMAGEN Y SONIDO", "2503" => "2503 REALI.", "2601" => "2601 CARPINTERIA Y MUEBLE", "2602" => "2602 MECANIZADO DE LA MADERA", "2701" => "2701 CULTIVOS MARINOS", "2801" => "2801 FABRICACION MECANICA", "2802" => "2802 FABRICACION SOLDADA", "2803" => "2803 MTO. DE MAQUINAS Y SIST. AUTOMATIC.", "2804" => "2804 MANTENIMIENTO EN LINEA", "2805" => "2805 OPERADOR DE MAQUINAS HERRAMIENTAS", "2901" => "2901 CONFECCION INDUSTRIAL", "3001" => "3001 ESTETICA FACIAL", "3002" => "3002 PELUQUERIA", "3101" => "3101 AUXILIAR DE LABORATORIO", "3102" => "3102 INDUSTRIAS ALIMENTARIAS", "3201" => "3201 ACT. FISICAS Y ANIMACION DEPORTIVA", "3202" => "3202 ANATOMIA PATOLOGIA-CITOLOGIA", "3203" => "3203 AUXILIAR DE ENFERMERIA", "3204" => "3204 SALUD AMBIENTAL", "3301" => "3301 EDUCADOR INFANTIL", "3401" => "3401 HILATURA Y TEJEDURIA", "3402" => "3402 PROCESOS QUIMICOS TEXTILES", "3501" => "3501 ALFARERIA Y CERAMICA", "3601" => "3601 ACT. INTERAREAS. ASPECTOS GENERALES", "3701" => "3701 TEMAS TRANSVERSALES. ASP. GENERALES", "3702" => "3702 EDUCACION AMBIENTAL", "3703" => "3703 EDUCACION DEL CONSUMIDOR", "3704" => "3704 EDUCACION MORAL Y CIVICA", "3705" => "3705 EDUCACION PARA LA IGUALDAD OPORTUN.", "3706" => "3706 EDUCACION PARA LA PAZ", "3707" => "3707 EDUCACION PARA LA SALUD", "3708" => "3708 EDUCACION VIAL", "3709" => "3709 PRENSA-ESCUELA", "3710" => "3710 OTROS TEMAS TRANSVERSALES", "3801" => "3801 NUEVAS TECNOLOGIAS. ASP. GENERALES", "3802" => "3802 MEDIOS INFORMATICOS", "3803" => "3803 MEDIOS AUDIOVISUALES", "3901" => "3901 ATENCION A LA DIVERSIDAD. ASP. GEN.", "3902" => "3902 OPTATIVIDAD", "3903" => "3903 DIVERSIFICACION Y ADAPT. CURRICULAR", "3904" => "3904 NECESIDADES EDUCATIVAS ESPECIALES", "3905" => "3905 ATENCION A MINORIAS ETNICAS", "4001" => "4001 ASPECTOS GENERALES", "4002" => "4002 INTERVENCION INSTITUCIONAL", "4003" => "4003 APOYO PSICOPEDAGOGICO", "4004" => "4004 ORIENTACION", "4005" => "4005 TUTORIA", "4101" => "4101 DIREC. Y GESTION. ASP. GENERALES", "4102" => "4102 PLANIF. Y EJECUCION PROY. Y PROGR.", "4103" => "4103 ESTRUC. ORGANIZ. TRABAJO Y GESTION", "4104" => "4104 LAS RELACIONES Y LA PARTICIPACION", "4105" => "4105 RECOGIDA", "4201" => "4201 ADMINIST. EDUCATIVA. ASP. GENERALES", "4202" => "4202 PLANIFICACION EDUCATIVA", "4203" => "4203 FUNCIONAMIENTO Y GESTION ADM. EDUC.", "4204" => "4204 HABILIDADES SOCIALES PARA INTERVEN.", "4301" => "4301 FORMACION PERMANENTE. ASP. GENER.", "4302" => "4302 MODELOS DE FORMACION", "4303" => "4303 MODALIDADES Y ESTRATEGIAS DE FORM.", "4304" => "4304 RECOGIDA", "4305" => "4305 HABILIDADES SOCIALES PARA INTERVEN.", "9901" => "9901 MATERIA DE MIGRACION");

$NIVELES_EDUCATIVOS = array('19' => '19 COMPENSATORIA', '17' => '17 EDUCACION DE PERSONAS ADULTAS', '18' => '18 EDUCACION ESPECIAL O INTEGRACION', '10' => '10 EDUCACION INFANTIL', '12' => '12 EDUCACION INFANTIL Y PRIMARIA', '15' => '15 ENSEÑANZAS ARTISTICAS', '21' => '21 EQUIPOS DE ORIENTACION', '20' => '20 EQUIPOS DIRECTIVOS', '22' => '22 EQUIPOS INTERDISCIPLINARES', '16' => '16 ESCUELAS OFICIALES DE IDIOMAS', '14' => '14 FORMACION PROFESIONAL', '27' => '27 FORMACION PROFESIONAL GRADO ESPECIFICA', '26' => '26 FORMACION PROFESIONAL GRADO MEDIO', '24' => '24 OTROS NIVELES / OTROS COLECTIVOS', '23' => '23 PERSONAL RED DE FORM. PERMANENTE', '11' => '11 PRIMARIA', '13' => '13 SECUNDARIA', '25' => '25 TODOS LOS PROFESORES CUALQUIER NIV.');

$CUERPOS_DOCENTES = array("0000" => "0000 NO FUNCIONARIO", "0590" => "0590 PROFESORES DE ENSEÑANZA SECUNDARIA", "0591" => "0591 PROF. TECNICOS FORMACION PROFESIONAL", "0592" => "0592 PROFESORES DE ESC. OFICIALES DE IDIOMAS", "0593" => "0593 CATEDRATICOS DE MUSICA Y ARTES ESCENICAS", "0594" => "0594 PROFESORES DE MUSICA Y ARTES ESCENICAS", "0595" => "0595 PROFESORES DE ARTES PLASTICAS Y DISEÑO", "0596" => "0596 MAESTROS DE TALLER ARTES PLAST. Y DISEÑO", "0597" => "0597 MAESTROS", "5407" => "5407 ESCALA DOCENTE GRUPO A DE LA AISS", "5423" => "5423 ESCALA DOCENTE GRUPO B DE LA AISS", "6470" => "6470 PROF.NUMER. Y PSICOL. ENS.INTEGR.", "6471" => "6471 PROF.MATER.TECN.-PROF.Y EDUC.E.I.", "6472" => "6472 PROF.PRACTICAS Y ACTIVIDADES E.I.", "7100" => "7100 INSPECTORES DE EDUCACION", "7110" => "7110 INSPECTORES AL SERVICIO ADMON. EDUCATIVA", "8100" => "8100 PROFESORES UNIVERSITARIOS", "8110" => "8110 ESCALA DE TEC.DE GESTIÓN DE U.SEVILLA", "8120" => "8120 ESCALA TEC. DE GESTIÓN DE U.DE CADIZ", "8121" => "8121 OTROS PROFESORES/OTROS PROFESIONALES", "8122" => "8122 OTROS FUNCIONARIOS DE LA ADMINISTRACION", "8123" => "8123 CATEDRÁTICOS DE UNIVERSIDAD");

#$MGM_ITE_ESPECS = array("PS. Tecnología.", "PS. Administración de Empresas.", "PS. Análisis y Química Industrial.", "PS. Construcciones Civiles y Edificación.", "PS. Formación y Orientación Laboral.", "PS. Hostelería y Turismo.", "PS. Informática.", "PS. Intervención Sociocomunitaria.", "PS. Navegación e Instalaciones Marinas.", "PS. Organización y Gestión Comercial.", "PS. Organización y Procesos de Mantenimiento de Vehículos.", "PS. Organización y Proyectos de Fabricación Mecánica.", "PS. Organización y Proyectos de Sistemas Energéticos.", "PS. Procesos de Producción Agraria.", "PS. Procesos en la Industria Alimentaria", "PS. Procesos Sanitarios.", "PS. Procesos y Productos de Textil, Confección y Piel.", "PS. Procesos y Productos de Vidrio y Cerámica.", "PS. Procesos y Productos en Artes Gráficas.", "PS. Procesos y Productos en Madera y Mueble.", "PS. Sistemas Electrónicos.", "PS. Sistemas Electrotécnicos y Automáticos.", "PS. Cocina y Pastelería.", "PS. Estética.", "PS. Fabricación e Instalación de Carpintería y Mueble.", "PS. Mantenimiento de Vehículos.", "PS. Mecanizado y Mantenimiento de Máquinas.", "PS. Patronaje y Confección.", "PS. Peluquería", "PS. Producción en Artes Gráficas.", "PS. Servicios de Restauración.", "PS. Soldadura.", "PMAE. Música.", "PMAE. Danza.", "PAPD. Conservación y restauración de materiales arqueológicos.", "PAPD. Conservación y restauración de obras escultóricas.", "PAPD. Conservación y restauración de obras pictóricas.", "PAPD. Conservación y restauración de Textiles.", "PAPD. Conservación y Restauración del documento gráfico.", "PAPD. Cerámica.", "PAPD. Diseño de interiores.", "PAPD. Diseño de moda.", "PAPD. Diseño de producto.", "PAPD. Diseño gráfico.", "PAPD. Vidrio.", "MTAPD. Artesanía y ornamentación con elementos vegetales.", "MTAPD. Bordados y encajes.", "MTAPD. Complementos y accesorios.", "MTAPD. Dorado y Policromía.", "MTAPD. Ebanistería Artística.", "MTAPD. Encuadernación Artística.", "MTAPD. Esmaltes.", "MTAPD. Fotografía y Procesos de reproducción.", "MTAPD. Modelismo y Maquetismo.", "MTAPD. Moldes y reproducciones.", "MTAPD. Musivaria.", "MTAPD. Talla en piedra y madera.", "MTAPD. Técnicas cerámicas.", "MTAPD. Técnicas de grabado y estampación.", "MTAPD. Técnicas de joyería y bisutería.", "MTAPD. Técnicas de orfebrería y platería.", "MTAPD. Técnicas de patronaje y confección.", "MTAPD. Técnicas del metal.", "MTAPD. Técnicas murales.", "MTAPD. Técnicas textiles.", "MTAPD. Técnicas vidrieras.", "M. Educación Infantil.", "M. Primaria.", "M. Pedagogía Terapéutica.", "M. Audición y Lenguaje.", "M. Educación Física.", "M. Música.", "M. Idioma extranjero: Inglés.", "M. Idioma extranjero: Francés.", "CoEOI. Alemán.", "CoEOI. Árabe", "CoEOI.Chino", "CoEOI.Danés", "CoEOI.Español Lengua Extranjera.", "CoEOI. Finés.", "CoEOI. Francés.", "CoEOI. Griego.", "CoEOI. Inglés.", "CoEOI. Irlandés.", "CoEOI. Italiano.", "CoEOI. Japonés.", "CoEOI. Neerlandés.", "CoEOI. Portugués", "CoEOI. Rumano.", "CoEOI. Ruso.", "CoEOI. Sueco.", "CoEOI. Lenguas cooficiales de las Comunidades Autónomas");

$MGM_ITE_ESPECS = array("TECNOLOGÍA","ADMINISTRACIÓN DE EMPRESAS","ANÁLISIS Y QUÍMICA INDUSTRIAL","CONSTRUCCIONES CIVILES Y EDIFICACIÓN","FORMACIÓN Y ORIENTACIÓN LABORAL","HOSTELERÍA Y TURISMO","INFORMÁTICA","INTERVENCIÓN SOCIOCOMUNITARIA","NAVEGACIÓN E INSTALACIONES MARINAS","ORGANIZACIÓN Y GESTIÓN COMERCIAL","ORGANIZACIÓN Y PROCESOS DE MANTENIMIENTO DE VEHÍCULOS","ORGANIZACIÓN Y PROYECTOS DE FABRICACIÓN MECÁNICA","ORGANIZACIÓN Y PROYECTOS DE SISTEMAS ENERGÉTICOS","PROCESOS DE PRODUCCIÓN AGRARIA","PROCESOS EN LA INDUSTRIA ALIMENTARÍA","PROCESOS SANITARIOS","PROCESOS Y PRODUCTOS DE TEXTIL, CONFECCIÓN Y PIEL","PROCESOS Y PRODUCTOS DE VIDRIO Y CERÁMICA","PROCESOS Y PRODUCTOS EN ARTES GRÁFICAS","PROCESOS Y PRODUCTOS EN MADERA Y MUEBLE","SISTEMAS ELECTRÓNICOS","SISTEMAS ELECTROTÉCNICOS Y AUTOMÁTICOS","COCINA Y PASTELERÍA","ESTÉTICA","FABRICACIÓN E INSTALACIÓN DE CARPINTERÍA Y MUEBLE","MANTENIMIENTO DE VEHÍCULOS","MECANIZADO Y MANTENIMIENTO DE MÁQUINAS","PATRONAJE Y CONFECCIÓN","PELUQUERÍA","PRODUCCIÓN EN ARTES GRÁFICAS","SERVICIOS DE RESTAURACIÓN","SOLDADURA","MÚSICA","DANZA CLÁSICA","CONSERVACIÓN Y RESTAURACIÓN DE MATERIALES ARQUEOLÓGICOS","CONSERVACIÓN Y RESTAURACIÓN DE OBRAS ESCULTÓRICAS","CONSERVACIÓN Y RESTAURACIÓN DE OBRAS PICTÓRICAS","CONSERVACIÓN Y RESTAURACIÓN DE TEXTILES","CONSERVACIÓN Y RESTAURACIÓN DEL DOCUMENTO GRÁFICO","CERÁMICA","DISEÑO DE INTERIORES","DISEÑO DE MODA","DISEÑO DE PRODUCTO","DISEÑO GRÁFICO","VIDRIO","ARTESANÍA Y ORNAMENTACIÓN DE ELEMENTOS VEGETALES","BORDADOS Y ENCAJES","COMPLEMENTOS Y ACCESORIOS","DORADO Y POLICROMÍA","EBANISTERÍA ARTÍSTICA","ENCUADERNACIÓN ARTÍSTICA","ESMALTES","FOTOGRAFÍA Y PROCESOS DE REPRODUCCIÓN","MODELISMO Y MAQUETISMO","MOLDES Y REPRODUCCIONES","MUSIVARIA","TALLA EN PIEDRA Y MADERA","TÉCNICAS CERÁMICAS","TÉCNICAS DE GRABADO Y ESTAMPACIÓN","TÉCNICAS DE JOYERÍA Y BISUTERÍA","TÉCNICAS DE ORFEBRERÍA Y PLATERÍA","TÉCNICAS DE PATRONAJE Y CONFECCIÓN","TÉCNICAS DEL METAL","TÉCNICAS MURALES","TÉCNICAS TEXTILES","TÉCNICAS VIDRIERAS","EDUCACIÓN INFANTIL","EDUCACIÓN PRIMARIA","PEDAGOGÍA TERAPEÚTICA","AUDICIÓN Y LENGUAJE","EDUCACIÓN FÍSICA","INTERPRETACIÓN EN EL MUSICAL","LENGUA EXTRANJERA: INGLÉS","LENGUA EXTRANJERA: FRANCÉS","ALEMÁN","ÁRABE","CHINO","DANÉS","ESPAÑOL LENGUA EXTRANJERA","FINES","FRANCÉS","GRIEGO","INGLÉS","IRLANDES","ITALIANO","JAPONÉS","NEERLANDÉS","PORTUGUÉS","RUMANO","RUSO","SUECO","LENGUAS COOFICIALES DE LAS COMUNIDADES AUTÓNOMAS","ACORDEÓN","ACROBACIA","ARPA","ASESORÍA Y PROCESOS DE IMAGEN PERSONAL","BIOLOGÍA Y GEOLOGÍA","CANTO","CANTO APLICADO AL ARTE DRAMÁTICO","CARACTERIZACIÓN E INDUMENTARIA","CATALÁN","CLARINETE","CLAVE","CONTRABAJO","CORO","DANZA APLICADA AL ARTE DRAMÁTICO","DANZA CONTEMPORÁNEA","DANZA ESPAÑOLA","DIBUJO","DIBUJO ARTÍSTICO Y COLOR","DIBUJO TÉCNICO","DICCIÓN Y EXPRESIÓN ORAL","DIRECCIÓN ESCÉNICA","DISEÑO TEXTIL","DRAMATURGIA","ECONOMÍA","EDICIÓN DE ARTE","EQUIPOS ELECTRÓNICOS","ESGRIMA","ESPACIO ESCÉNICO","EUSKERA","EXPRESIÓN CORPORAL","FAGOT","FILOSOFÍA","FÍSICA Y QUÍMICA","FLABIOL I TAMBORI","FLAMENCO","FLAUTA DE PICO","FLAUTA TRAVESERA","FOTOGRAFÍA","FUNDAMENTOS DE COMPOSICIÓN","GAITA GALEGA","GALLEGO","GEOGRAFÍA E HISTORIA","GUITARRA","GUITARRA FLAMENCA","HISTORIA DE LA DANZA","HISTORIA DE LA MÚSICA","HISTORIA DEL ARTE","ILUMINACIÓN","INSTALACIÓN Y MANTENIMIENTO DE EQUIPOS TÉRMICOS Y DE FLUIDOS","INSTALACIONES ELECTROTÉCNICAS","INSTALACIONES Y EQUIPOS DE CRÍA Y CULTIVO","INSTRUMENTOS DE CUERDA PULSADA DEL RENACIMIENTO Y DEL BARROCO","INSTRUMENTOS DE PUA","INTERPRETACIÓN","INTERPRETACIÓN CON OBJETOS","INTERPRETACIÓN EN EL TEATRO DEL GESTO","JOYERÍA Y ORFEBRERÍA","LABORATORIO","LATÍN","LENGUA ARANESA","LENGUA CASTELLANA Y LITERATURA","LENGUA CATALANA Y LITERATURA","LENGUA EXTRANJERA: ALEMÁN","LENGUA Y LITERATURA CATALANAS (ISLAS BALEARES)","LENGUA Y LITERATURA GALLEGA","LENGUA Y LITERATURA VALENCIANA","LENGUA Y LITERATURA VASCA","LENGUA Y LITERATURA VASCA (NAVARRA)","LENGUAJE MUSICAL","LITERATURA DRAMÁTICA","MÁQUINAS, SERVICIOS Y PRODUCCIÓN","MATEMÁTICAS","MATERIALES Y TECNOLOGÍA: CERÁMICA Y VIDRIO","MATERIALES Y TECNOLOGÍA: CONSERVACIÓN Y RESTAURACIÓN","MATERIALES Y TECNOLOGÍA: DISEÑO","MEDIOS AUDIOVISUALES","MEDIOS INFORMÁTICOS","OBOE","OFICINA DE PROYECTOS DE CONSTRUCCIÓN","OFICINA DE PROYECTOS DE FABRICACIÓN MECÁNICA","OPERACIONES DE PROCESOS","OPERACIONES DE PRODUCCIÓN AGRARIA","OPERACIONES Y EQUIPOS DE ELABORACIÓN DE PRODUCTOS ALIMENTARIOS","ORGANIZACIÓN INDUSTRIAL Y LEGISLACIÓN","ÓRGANO","ORIENTACIÓN EDUCATIVA","ORQUESTA","PERCUSIÓN","PIANO","PROCEDIMIENTOS DE DIAGNÓSTICO CLÍNICO Y ORTOPROTÉSICO","PROCEDIMIENTOS SANITARIOS Y ASISTENCIALES","PROCESOS COMERCIALES","PROCESOS DE CULTIVO ACUÍCOLA","PROCESOS DE GESTIÓN ADMINISTRATIVA","PROCESOS DIAGNÓSTICOS CLÍNICOS Y PRODUCTOS ORTOPROTÉSICOS","PROCESOS Y MEDIOS DE COMUNICACIÓN","PRODUCCIÓN TEXTIL Y TRATAMIENTOS FÍSICO-QUÍMICOS","SAXOFÓN","SERVICIOS A LA COMUNIDAD","SISTEMAS Y APLICACIONES INFORMÁTICAS","TÉCNICAS ESCÉNICAS","TÉCNICAS GRÁFICAS","TÉCNICAS Y PROCEDIMIENTOS DE IMAGEN Y SONIDO","TENORA Y TIBLE","TEORÍA E HISTORIA DEL ARTE","TEORÍA TEATRAL","TROMBÓN","TROMPA","TROMPETA","TUBA","TXISTU","VALENCIANO","VASCUENCE (NAVARRA)","VIOLA","VIOLA DA GAMBA","VIOLÍN","VIOLONCELLO","VOLUMEN");
$MGM_ITE_CUERPOS_ESPECS=array('0000'=>array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173,174,175,176,177,178,179,180,181,182,183,184,185,186,187,188,189,190,191,192,193,194,195,196,197,198,199,200,201,202,203,204,205,206,207,208,209),'0596'=>array(45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65), '0595'=>array(34,35,36,37,38,39,40,41,42,43,44,109,110,113,116,129,138,148,164,165,166,167,168,175,209), '0594'=>array(33,71,92,93,94,97,98,99,101,102,103,104,105,106,107,111,112,114,118,119,121,122,125,126,127,128,130,131,134,135,136,137,139,143,144,145,146,147,160,161,169,176,178,179,180,189,192,193,195,196,197,198,199,200,201,202,205,207,207,208) , '0592'=>array(74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90,100,120,132,203), '0591'=>array(22,23,24,25,26,27,28,29,30,31,117,140,141,142,149,162,170,171,172,173,174,181,182,183,185,188,190,191,194), '0597'=>array(32,66,67,68,69,70,74,80,82,120,204), '0590'=>array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,32,70,74,80,81,82,84,87,95,96,108,115,123,124,133,150,151,152,153,155,156,157,158,159,163,177,184,186,187));
$COMUNIDADES = array(0 => 'NINGUNA', 1 => 'ANDALUCIA', 2 => 'ARAGON', 3 => 'ASTURIAS', 4 => 'BALEARES', 5 => 'CANARIAS', 6 => 'CANTABRIA', 7 => 'CASTILLA Y LEON', 8 => 'CASTILLA LA MANCHA', 9 => 'CATALUÑA', 10 => 'VALENCIA', 11 => 'EXTREMADURA', 12 => 'GALICIA', 13 => 'MADRID', 14 => 'MURCIA', 15 => 'NAVARRA', 16 => 'EUSKADI', 17 => 'RIOJA', 18 => 'CEUTA', 19 => 'MELILLA');

$PROV_COMUNIDADES = array(01 => array(04 => 'Almería', 11 => 'Cádiz', 14 => 'Córdoba', 18 => 'Granada', 21 => 'Huelva', 23 => 'Jaén', 29 => 'Málaga', 41 => 'Sevilla'), 02 => array(22 => 'Huesca', 44 => 'Teruel', 50 => 'Zaragoza'), 03 => array(33 => 'Asturias'), 04 => array(07 => 'Islas Baleares'), 05 => array(35 => 'Las Palmas', 38 => 'Santa Cruz de Tenerife'), 06 => array(39 => 'Cantabria'), 07 => array(05 => 'Ávila', 09 => 'Burgos', 24 => 'León', 34 => 'Palencia', 37 => 'Salamanca', 40 => 'Segovia', 42 => 'Soria', 47 => 'Valladolid', 49 => 'Zamora'), 08 => array(02 => 'Albacete', 13 => 'Ciudad Real', 16 => 'Cuenca', 19 => 'Guadalajara', 45 => 'Toledo'), 09 => array(08 => 'Barcelona', 17 => 'Girona', 25 => 'Lleida', 43 => 'Tarragona'), 10 => array(03 => 'Alicante', 12 => 'Castellón', 46 => 'Valencia'), 11 => array(10 => 'Cáceres', 06 => 'Badajoz'), 12 => array(15 => 'A Coruña', 27 => 'Lugo', 32 => 'Ourense', 36 => 'Pontevedra', ), 13 => array(28 => 'Madrid'), 14 => array(30 => 'Murcia'), 15 => array(31 => 'Navarra'), 16 => array(01 => 'Álava', 20 => 'Guipúzcoa', 48 => 'Vizcaya'), 17 => array(26 => 'La Rioja'), 18 => array(51 => 'Ceuta'), 19 => array(52 => 'Melilla'));
$COMUNIDADES_CC = array('04' => 'ANDALUCIA', '11' => 'ANDALUCIA', '14' => 'ANDALUCIA', '18' => 'ANDALUCIA', '21' => 'ANDALUCIA', '23' => 'ANDALUCIA', '29' => 'ANDALUCIA', '41' => 'ANDALUCIA', '22' => 'Aragon', '44' => 'Aragon', '50' => 'Aragon', '33' => 'Asturias', '07' => 'Baleares', '35' => 'Canarias', '38' => 'Canarias', '39' => 'Cantabria', '05' => 'Castilla Leon', '09' => 'Castilla Leon', '24' => 'Castilla Leon', '34' => 'Castilla Leon', '37' => 'Castilla Leon', '40' => 'Castilla Leon', '42' => 'Castilla Leon', '47' => 'Castilla Leon', '49' => 'Castilla Leon', '02' => 'C. Mancha', '13' => 'C. Mancha', '16' => 'C. Mancha', '19' => 'C. Mancha', '45' => 'C. Mancha', '08' => 'Cataluña', '17' => 'Cataluña', '25' => 'Cataluña', '43' => 'Cataluña', '03' => 'Valencia', '12' => 'Valencia', '46' => 'Valencia', '10' => 'Extremadura', '06' => 'Extremadura', '15' => 'Galicia', '27' => 'Galicia', '32' => 'Galicia', '36' => 'Galicia', '28' => 'Madrid', '30' => 'Murcia', '31' => 'Navarra', '01' => 'Euskadi', '20' => 'Euskadi', '48' => 'Euskadi', '26' => 'La Rioja', '51' => 'Ceuta', '52' => 'Melilla');
$CSV_CACHED_FILE = array();
$MEC_PATTERN="/^51|^52|^60|28923065|^00*25|^00*50|^00*15|^00*35/";  //Ceuta, Melilla, Extranjero, Interno, Itinerantes, extranjero, iberoamerica, eTwinning

/**
 * Checks if an user can perform the view action on module access
 * @param object $cm
 * @return boolean
 */
function mgm_can_do_view($cm = 0) {
    if($cm) {
        $context = get_context_instance(CONTEXT_MODULE, $cm -> id);
    } else {
        $context = get_context_instance(CONTEXT_SYSTEM);
    }

    return (has_capability('mod/mgm:viewedicion', $context));
}

/**
 * Checks if an user can perform the create action on module access
 * @param object $cm
 * @return boolean
 */
function mgm_can_do_create($cm = 0) {
    if($cm) {
        $context = get_context_instance(CONTEXT_MODULE, $cm -> id);
    } else {
        $context = get_context_instance(CONTEXT_SYSTEM);
    }

    return (has_capability('mod/mgm:createedicion', $context));
}

/**
 * Checks if an user can perform the edit action on module access
 * @param object $cm
 * @return boolean
 */
function mgm_can_do_edit($cm = 0) {
    if($cm) {
        $context = get_context_instance(CONTEXT_MODULE, $cm -> id);
    } else {
        $context = get_context_instance(CONTEXT_SYSTEM);
    }

    return (has_capability('mod/mgm:editedicion', $context));
}

/**
 * Checks if an user can perform the assigncriteria action on module access
 * @param object $cm
 * @return boolean
 */
function mgm_can_do_assigncriteria($cm = 0) {
    if($cm) {
        $context = get_context_instance(CONTEXT_MODULE, $cm -> id);
    } else {
        $context = get_context_instance(CONTEXT_SYSTEM);
    }

    return (has_capability('mod/mgm:assigncriteria', $context));
}

/**
 * Checks if an user can perform the aprobe action on module access
 * @param object $cm
 * @return boolean
 */
function mgm_can_do_aprobe($cm = 0) {
    if($cm) {
        $context = get_context_instance(CONTEXT_MODULE, $cm -> id);
    } else {
        $context = get_context_instance(CONTEXT_SYSTEM);
    }

    return (has_capability('mod/mgm:aprobe', $context));
}

/**
 * Prints the turn editing on/off button on mod/mgm/index.php
 *
 * @param integer $editionid The id of the edition we are showing or 0 for system context
 * @return string HTML of the editing button, or empty string if the user is not allowed to see it.
 */
function mgm_update_edition_button($editionid = 0) {
    global $CFG, $USER, $OUTPUT;

    // Check permissions.
    if(!mgm_can_do_create()) {
        return '';
    }

    // Work out the appropiate action.
    if(!empty($USER -> editionediting)) {
        $label = get_string('turneditingoff');
        $edit = 'off';
    } else {
        $label = get_string('turneditingon');
        $edit = 'on';
    }

    // Generate the button HTML.
    $options = array('editionedit' => $edit, 'sesskey' => sesskey());
    if($editionid) {
        $options['id'] = $editionid;
        $page = 'edition.php';
    } else {
        $page = 'index.php';
    }
	
    return $OUTPUT->single_button(new moodle_url($CFG -> wwwroot . '/mod/mgm/' . $page, $options), $label, 'get');
}

function mgm_get_edition_data($edition) {
	global $DB;
    if(!is_object($edition)) {
        return NULL;
    }

    $fulledition = array('edition' => $edition,
    		              'courses' => $DB->get_record('edicion_course', 'edicionid', $edition -> id),
    		              'criteria' => $DB->get_record('edicion_criterios', 'edicion', $edition -> id),
						  'preinscripcion' => $DB->get_record('edicion_preinscripcion', 'edicionid', $edition -> id),
    		              'inscripcion' => $DB->get_record('edicion_inscripcion', 'edicionid', $edition -> id));

    return $fulledition;
}

/**
 * Return the number of courses in an edition.
 *
 * @param object $edition
 * @return string The number of courses in an edition
 */
function mgm_count_courses($edition) {
    global $CFG, $DB;

    if(!is_object($edition)) {
        return '';
    }

    $sql = "SELECT COUNT(d.id) FROM " . $CFG -> prefix . "edicion_course d
    		WHERE d.edicionid = $edition->id";

    return $DB->get_field_sql($sql);
}

function mgm_get_edition_course($editionid, $courseid) {
	global $DB;
    return $DB->get_record('edicion_course', array('edicionid'=> $editionid, 'courseid'=> $courseid));
}

/**
 * Return an edition HTML link
 * @param object $edition
 * @return string
 */
function mgm_get_edition_link($edition) {
    global $CFG;

    if(!is_object($edition)) {
        return '';
    }

    return '<a title="' . get_string('edit') . '" href="edicionedit.php?id=' . $edition -> id . '">' . $edition -> name . '</a>';
}

/**
 * Return the edition's actions menu HTML code
 *
 * @param object $edition
 * @return string
 */
function mgm_get_edition_menu($edition) {
    global $CFG, $OUTPUT;
    
    if(!is_object($edition)) {
        return '';
    }

    $menu = '<a title="' . get_string('edit') . '" href="edicionedit.php?id=' . $edition -> id . '"><img' .
            ' src="' . $OUTPUT->pix_url('t/edit') . '" class="iconsmall" alt="' . get_string('edit') . '" /></a>';
    $menu .= ' | ';
    $menu .= '<a title="' . get_string('delete') . '" href="delete.php?id=' . $edition -> id . '">' .
             '<img src="' . $OUTPUT->pix_url('t/delete') . '" class="iconsmall" alt="' . get_string('delete') . '" /></a>';
    $menu .= ' | ';

    if(mgm_edition_is_active($edition)) {
        $menu .= '<a title="' . get_string('desactivar', 'mgm') . '" href="active.php?id=' . $edition -> id . '">' .
                 '<img src="' . $OUTPUT->pix_url('t/stop'). '" class="iconsmall" alt="' . get_string('desactivar', 'mgm') . '" /></a>';
    } else {
        $menu .= '<a title="' . get_string('activar', 'mgm') . '" href="active.php?id=' . $edition -> id . '">' . '<img src="' . $OUTPUT->pix_url('/t/go') . '" class="iconsmall" alt="' . get_string('activar', 'mgm') . '" /></a>';
    }
    if(!mgm_edition_is_certified($edition)) {
        $menu .= ' | <a title="' . get_string('cert', 'mgm') . '" href="certificate.php?id=' . $edition -> id . '" id="edicion_' . $edition -> id . '">' .
                 '<img src="' . $OUTPUT->pix_url('/t/grades') . '" class="iconsmall" alt="' . get_string('cert', 'mgm') . '" /></a>';
        $menu .= ' | <a title="' . get_string('pago-nc', 'mgm') . '" href="#">' . '<img src="' . $OUTPUT->pix_url('/i/unlock') . '" class="iconsmall" alt="' . get_string('pago-nc', 'mgm') . '" /></a>';
    } else {
        if(mgm_edition_is_on_draft($edition)) {
            $menu .= ' | <a title="' . get_string('certdraft', 'mgm') . '" href="certificate.php?id=' . $edition -> id . '&draft=1" id="edicion_' . $edition -> id . '">'.
                     '<img src="' . $OUTPUT->pix_url('/c/site') . '" class="iconsmall" alt="' . get_string('certdraft', 'mgm') . '" /></a>';
            $menu .= ' | <a title="'.get_string('pago-nc', 'mgm').'" href="#">'.'<img src="' . $OUTPUT->pix_url('/i/unlock') . '" class="iconsmall" alt="'.get_string('pago-nc', 'mgm').'" /></a>';
        }

        if(mgm_edition_is_on_validate($edition)) {
            $menu .= ' | <a title="'.get_string('certified', 'mgm').'" href="#">'.
                     '<img src="' . $OUTPUT->pix_url('/i/tick_green_small') . '" class="iconsmall" alt="'.get_string('certified', 'mgm').'" /></a>';
            if ($edition->paid){
            	$menu .= ' | <a title="'.get_string('pago', 'mgm').'" href="fees.php?edition='.$edition->id.'&multiple=1">'.
                     '<img src="'. $OUTPUT->pix_url('/i/lock') .'" class="iconsmall" alt="'.get_string('pago', 'mgm').'" /></a>';
            }else{
            	$menu .= ' | <a title="'.get_string('do_paid', 'mgm').'" href="fees.php?change_state=1&edition='.$edition->id.'">'.
                     '<img src="'. $OUTPUT->pix_url('/i/lock').'" class="iconsmall" alt="'.get_string('do_paid', 'mgm').'" /></a>';
            }
        }
    }

    return $menu;
}

function mgm_print_ediciones_list() {
    global $CFG, $DB, $OUTPUT;

    $editions = $DB->get_records('edicion');

    $editionimage = '<img src="' . $OUTPUT->pix_url('/i/db') .'" alt="" />';
    $courseimage = '<img src="' . $OUTPUT->pix_url('/i/course') .'" alt="" />';
    $table = '<table class="mod-mgm editionlist">';
    foreach($editions as $edition) {
        $table .= '<tr>';
        $table .= '<td valign="top" class="mod-mgm edition image">' . $editionimage . '</td>';
        $table .= '<td valign="top" class="mod-mgm edition name">';
        $table .= format_string($edition -> name);
        $table .= '</td>';
        $table .= '<td class="mod-mgm edition info">&nbsp;</td>';
        if(mgm_count_courses($edition) > 0) {
            foreach(mgm_get_edition_courses($edition) as $course) {
                $table .= '<tr>';
                $table .= '<td valign="top" class="mod-mgm edition image course">' . $courseimage . '</td>';
                $table .= '<td valign="top" class="mod-mgm edition name course">';
                $table .= '<a class="mod-mgm edition link course" href="' . $CFG -> wwwroot . '/mod/mgm/courses.php?courseid=' . $course -> id . '&edicionid=' . $edition -> id . '">' . format_string($course -> fullname) . '</a>';
                if($cr = mgm_exists_criteria_for_course($edition, $course)) {
                    $table .= ' <span style="font-size: 9pt;">(Criterios Fijados: ' . $cr . ' )</span>';
                } else {
                    $table .= ' <span style="font-size: 9pt;">(Criterios no Fijados)</span>';
                }
                $table .= '</td>';
                $table .= '<td class="mod-mgm course info">&nbsp;</td>';
                $table .= '</tr>';
            }
        }
        $table .= '</tr>';
    }
    $table .= '</table>';

    echo $table;
}

function mgm_print_whole_ediciones_list() {
    global $CFG, $DB;

    $editions = $DB->get_records('edicion');

    $editionimage = '<img src="' . $CFG -> pixpath . '/i/db.gif" alt="" />';
    $courseimage = '<img src="' . $CFG -> pixpath . '/i/course.gif" alt="" />';
    $table = '<table class="mod-mgm editionlist">';
    foreach($editions as $edition) {
        $table .= '<tr>';
        $table .= '<td valign="top" class="mod-mgm edition image">' . $editionimage . '</td>';
        $table .= '<td valign="top" class="mod-mgm edition name">';
        $table .= '<a class="mod-mgm edition link" href="' . $CFG -> wwwroot . '/mod/mgm/view.php?id=' . $edition -> id . '">' . format_string($edition -> name) . '</a>';
        $table .= '</td>';
        $table .= '<td class="mod-mgm edition info">&nbsp;</td>';
        if(mgm_count_courses($edition) > 0) {
            foreach(mgm_get_edition_courses($edition) as $course) {
                $table .= '<tr>';
                $table .= '<td valign="top" class="mod-mgm edition image course">' . $courseimage . '</td>';
                $table .= '<td valign="top" class="mod-mgm edition name course">';
                $table .= '<a class="mod-mgm edition link course" href="' . $CFG -> wwwroot . '/course/view.php?id=' . $course -> id . '">' . format_string($course -> fullname) . '</a>';
                $table .= '</td>';
                $table .= '<td class="mod-mgm course info">&nbsp;</td>';
                $table .= '</tr>';
            }
        }
        $table .= '</tr>';
    }
    $table .= '</table>';

    echo $table;
}

function mgm_print_fees_ediciones_list() {
    global $CFG, $DB;

    $editions = $DB->get_records('edicion');

    $editionimage = '<img src="' . $CFG -> pixpath . '/i/db.gif" alt="" />';
    $courseimage = '<img src="' . $CFG -> pixpath . '/i/course.gif" alt="" />';
    $table = '<table class="mod-mgm editionlist">';
    foreach($editions as $edition) {
        $table .= '<tr>';
        $table .= '<td valign="top" class="mod-mgm edition image">' . $editionimage . '</td>';
        $table .= '<td valign="top" class="mod-mgm edition name">';
        $table .= '<a class="mod-mgm edition link" href="'.$CFG->wwwroot.'/mod/mgm/fees.php?edition='.$edition->id.'&multiple=1">';
        $table .= format_string($edition -> name);
        $table .= '</a>';
        $table .= '</td>';
        $table .= '<td class="mod-mgm edition info">&nbsp;</td>';
        if(mgm_count_courses($edition) > 0) {
            foreach(mgm_get_edition_courses($edition) as $course) {
                $table .= '<tr>';
                $table .= '<td valign="top" class="mod-mgm edition image course">' . $courseimage . '</td>';
                $table .= '<td valign="top" class="mod-mgm edition name course">';
                $table .= '<a class="mod-mgm edition link course" href="'.$CFG->wwwroot.'/mod/mgm/fees.php?id='.$course->id.'&edicionid='.$edition->id.'">'.format_string($course -> fullname).'</a>';
                $table .= '</td>';
                $table .= '<td class="mod-mgm course info">&nbsp;</td>';
                $table .= '</tr>';
            }
        }
        $table .= '</tr>';
    }
    $table .= '</table>';

    echo $table;
}

function mgm_get_edition_courses($edition) {
    global $CFG, $DB;

    if(!is_object($edition)) {
        return array();
    }

    $sql = "SELECT * FROM " . $CFG -> prefix . "course
    		WHERE id IN (
    			SELECT courseid FROM " . $CFG -> prefix . "edicion_course
    			WHERE edicionid=" . $edition -> id . "
    	    )
    	    ORDER BY fullname;";

    if(!$courses = $DB->get_records_sql($sql)) {
        return array();
    }

    return $courses;
}

function mgm_get_edition_available_courses($edition) {
    global $CFG, $DB;
//     require_once ("$CFG->dirroot/enrol/enrol.class.php");

//     $sql = "SELECT id, fullname, enrol FROM " . $CFG -> prefix . "course
    $sql = "SELECT id, fullname FROM " . $CFG -> prefix . "course
			WHERE id NOT IN (
				SELECT courseid FROM " . $CFG -> prefix . "edicion_course
				WHERE edicionid = " . $edition -> id . "
			) AND id NOT IN (
				SELECT courseid FROM " . $CFG -> prefix . "edicion_course
			) AND id != '1'
			ORDER BY fullname";
    $courses = $DB->get_records_sql($sql);
    $ret = array();
    foreach($courses as $course) {
    	
//         if(enrolment_factory::factory($course -> enrol) instanceof enrolment_plugin_mgm) {
            $ret[] = $course;
//         }
    }

    return $ret;
}

function mgm_add_course($edition, $courseid) {
    global $DB;

    if(!$DB->record_exists('edicion_course', array('edicionid'=> $edition -> id,'courseid'=> $courseid))) {
        $row = new stdClass();
        $row -> edicionid = $edition -> id;
        $row -> courseid = $courseid;
        $row -> codactividad = "0";

        return $DB->insert_record('edicion_course', $row);
    }

    return false;
}

function mgm_remove_course($edition, $courseid) {
	global $DB;
    if($row = mgm_get_edition_course($edition -> id, $courseid)) {
        $DB->delete_records('edicion_course', array('id'=> $row -> id));
        return true;
    }
    return false;
}

function mgm_update_edition($edition) {
	global $DB;
    $edition -> timemodified = time();
    $result = $DB->update_record('edicion', $edition);
    if($result) {
        events_trigger('edition_updated', $edition);
    }
    return $result;
}

function mgm_set_edition_paid($editionid){
	global $DB;
	$editon=new StdClass();
	$edition -> id = $editionid;
	$edition -> paid = 1;
    $result = $DB->update_record('edicion', $edition);
    if($result) {
        events_trigger('edition_updated', $edition);
    }
    return $result;
}

function mgm_create_edition($edition) {
	global $DB;
    $edition -> timecreated = time();
    $edition -> timemodified = time();
    $edition -> fechaemision = 0;
    $id = $DB->insert_record('edicion', $edition);
    if($result) {
        events_trigger('edition_created', get_record('edicion', 'id', $result));
    }
    return $result;
}

function mgm_delete_edition($editionorid) {
    global $CFG, $DB;
    $result = true;

    if(is_object($editionorid)) {
        $editionid = $editionorid -> id;
        $edition = $editionorid;
    } else {
        $editionid = $editionorid;
        if(!$edition = $DB->get_record('edition', 'id', $editionid)) {
            return false;
        }
    }
    if(!mgm_remove_edition_contents($editionid)) {
        $result = false;
    }
    if(!$DB->delete_records('edicion', 'id', $editionid)) {
        $result = false;
    }
    if($result) {
        // trigger events
        events_trigger('edition_deleted', $edition);
    }
}

function mgm_remove_edition_contents($editionid) {
	global $DB;
    if(!$editon = $DB->get_record('edicion', 'id', $editionid)) {
        error('Edition ID was incorrect (can\'t find it)');
    }
    $DB->delete_records('edicion_course', 'edicionid', $editionid);
    $DB->delete_records('edicion_criterios', 'edicion', $editionid);
    $DB->delete_records('edicion_inscripcion', 'edicionid', $editionid);
    $DB->delete_records('edicion_preinscripcion', 'edicionid', $editionid);
    return true;
}

function mgm_translate_especialidad($id) {
    global $CFG, $DB;

    $sql = "SELECT value FROM " . $CFG -> prefix . "edicion_ite
    		WHERE type = " . MGM_ITE_ESPECIALIDADES . "";
    $especialidades = explode("\n",  $DB->get_record_sql($sql) -> value);
    return ($id !== false && $id != '') ? $especialidades[$id] : '';
}

function mgm_get_edition_course_criteria($editionid, $courseid) {
    global $CFG, $DB;

    $criteria = new stdClass();
    $criteria -> plazas = 0;
    $criteria -> espec = array();

    //Datos extendidos
    $edata = mgm_get_edition_course($editionid, $courseid);
    $criteria -> codmodalidad = $edata -> codmodalidad;
    $criteria -> codagrupacion = $edata -> codagrupacion;
    $criteria -> codprovincia = $edata -> codprovincia;
    $criteria -> codpais = $edata -> codpais;
    $criteria -> codmateria = $edata -> codmateria;
    $criteria -> codniveleducativo = $edata -> codniveleducativo;
    $criteria -> numhoras = $edata -> numhoras;
    $criteria -> numcreditos = $edata -> numcreditos;
    $criteria -> fechainicio = $edata -> fechainicio;
    $criteria -> fechafin = $edata -> fechafin;
    $criteria -> localidad = $edata -> localidad;
    $criteria -> fechainimodalidad = $edata -> fechainimodalidad;

    $sql = 'SELECT * FROM ' . $CFG -> prefix . 'edicion_criterios
    		WHERE edicion = \'' . $editionid . '\' AND course = \'' . $courseid . '\'';
    if(!$cdata = $DB->get_records_sql($sql)) {
        return $criteria;
    }

    foreach($cdata as $c) {
        if($c -> type == MGM_CRITERIA_PLAZAS) {
            $criteria -> plazas = $c -> value;
        }

        // OBSOLETE v1.0
        /*if ($c->type == MGM_CRITERIA_CC) {
         $criteria->ccaas = $c->value;
         }*/

        if($c -> type == MGM_CRITERIA_OPCION1) {
            $criteria -> opcion1 = $c -> value;
        }

        if($c -> type == MGM_CRITERIA_OPCION2) {
            $criteria -> opcion2 = $c -> value;
        }

        if($c -> type == MGM_CRITERIA_ESPECIALIDAD) {
            $criteria -> espec[$c -> value] = mgm_translate_especialidad($c -> value);
        }

        /*if ($c->type == MGM_CRITERIA_MINGROUP) {
         $criteria->mingroup = $c->value;
         }

         if ($c->type == MGM_CRITERIA_MAXGROUP) {
         $criteria->maxgroup = $c->value;
         }*/

        if($c -> type == MGM_CRITERIA_DEPEND) {
            $criteria -> depends = true;
            $criteria -> dlist = $c -> value;
        }

        if($c->type == MGM_CRITERIA_NUMGROUPS) {
            $criteria->numgroups = $c->value;
        }

        if($c->type == MGM_CRITERIA_COMUNIDAD) {
            $criteria->comunidad = $c->value;
        }

        if($c->type == MGM_CRITERIA_PAGO) {
            $criteria->tutorpayment = $c->value;
        }

        if($c->type == MGM_CRITERIA_ECUADOR) {
            $criteria->ecuadortask = $c->value;
        }

        if($c->type == MGM_CRITERIA_DURATION) {
            $criteria->duration = $c->value;
        }

        if($c->type == MGM_CRITERIA_PREVLAB) {
            $criteria->prevlab = $c->value;
        }

        if($c->type == MGM_CRITERIA_TRAMOS) {
            $criteria->tramo = explode(',', $c->value);
        }
    }
    return $criteria;
}

function mgm_set_edition_course_criteria($data) {
    global $CFG, $DB;

    $criteria = new stdClass();
    $criteria -> edicion = $data -> edicionid;
    $criteria -> course = $data -> courseid;

    //Datos extendidos
    $edata = mgm_get_edition_course($data->edicionid, $data->courseid);
    $edata->codmodalidad = $data->codmodalidad;
    $edata->codagrupacion = $data->codagrupacion;
    $edata->codprovincia = $data->codprovincia;
    $edata->codpais = $data->codpais;
    $edata->codmateria = $data->codmateria;
    $edata->codniveleducativo = $data->codniveleducativo;
    $edata->numhoras = $data->numhoras;
    $edata->numcreditos = $data->numcreditos;
    $edata->fechainicio = $data->fechainicio;
    $edata->fechafin = $data->fechafin;
    $edata->localidad = $data->localidad;
    $edata->fechainimodalidad = $data ->fechainimodalidad;

    $DB->update_record('edicion_course', $edata);

    // Plazas
    $criteria -> type = MGM_CRITERIA_PLAZAS;
    $criteria -> value = $data -> plazas;
    if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
        $DB->insert_record('edicion_criterios', $criteria);
    } else {
        $criteria -> id = $criteriaid -> id;
        $DB->update_record('edicion_criterios', $criteria);
        unset($criteria -> id);
    }

    // CC
    // OBSOLETE v1.0
    /*$criteria->type = MGM_CRITERIA_CC;
     $criteria->value = $data->ccaas;
     if (!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
     insert_record('edicion_criterios', $criteria);
     } else {
     $criteria->id = $criteriaid->id;
     update_record('edicion_criterios', $criteria);
     unset($criteria->id);
     }*/

    // Opcion1
    $criteria -> type = MGM_CRITERIA_OPCION1;
    $criteria -> value = $data -> opcion1;
    if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
        $DB->insert_record('edicion_criterios', $criteria);
    } else {
        $criteria -> id = $criteriaid -> id;
        $DB->update_record('edicion_criterios', $criteria);
        unset($criteria -> id);
    }

    // Opcion2
    $criteria -> type = MGM_CRITERIA_OPCION2;
    $criteria -> value = $data -> opcion2;
    if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
        $DB->insert_record('edicion_criterios', $criteria);
    } else {
        $criteria -> id = $criteriaid -> id;
        $DB->update_record('edicion_criterios', $criteria);
        unset($criteria -> id);
    }
    /*
     // Mingroup
     $criteria->type = MGM_CRITERIA_MINGROUP;
     $criteria->value = $data->mingroup;
     if (!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
     insert_record('edicion_criterios', $criteria);
     } else {
     $criteria->id = $criteriaid->id;
     update_record('edicion_criterios', $criteria);
     unset($criteria->id);
     }

     // Maxgroup
     $criteria->type = MGM_CRITERIA_MAXGROUP;
     $criteria->value = $data->maxgroup;
     if (!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
     insert_record('edicion_criterios', $criteria);
     } else {
     $criteria->id = $criteriaid->id;
     update_record('edicion_criterios', $criteria);
     unset($criteria->id);
     }
     */

    // Comunidad
    $criteria -> type = MGM_CRITERIA_COMUNIDAD;
    $criteria -> value = $data -> comunidad;
    if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
        $DB->insert_record('edicion_criterios', $criteria);
    } else {
        $criteria -> id = $criteriaid -> id;
        $DB->update_record('edicion_criterios', $criteria);
        unset($criteria -> id);
    }

    // Numgroups
    $criteria -> type = MGM_CRITERIA_NUMGROUPS;
    $criteria -> value = $data -> numgroups;
    if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
        $DB->insert_record('edicion_criterios', $criteria);
    } else {
        $criteria -> id = $criteriaid -> id;
        $DB->update_record('edicion_criterios', $criteria);
        unset($criteria -> id);
    }

    // Dependencies
    if(isset($data -> dpendsgroup)) {
        $criteria -> type = MGM_CRITERIA_DEPEND;
        $criteria -> value = $data -> dpendsgroup['dlist'];
        if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
            $DB->insert_record('edicion_criterios', $criteria);
        } else {
            $criteria -> id = $criteriaid -> id;
            $DB->update_record('edicion_criterios', $criteria);
            unset($criteria -> id);
        }
    }else{//eliminar dependencia
    	$criteria -> type = MGM_CRITERIA_DEPEND;
        $criteria -> value = $data -> dpendsgroup['dlist'];
        if($criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
        	 $DB->delete_records('edicion_criterios', 'id', $criteriaid -> id);
        }
    }

    // Add especialidad
    if(isset($data -> aespecs)) {
        foreach($data->aespecs as $k => $v) {
            $criteria -> type = MGM_CRITERIA_ESPECIALIDAD;
            $criteria -> value = $v;
            if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
                $DB->insert_record('edicion_criterios', $criteria);
            } else {
                $criteria -> id = $criteriaid -> id;
                $DB->update_record('edicion_criterios', $criteria);
                unset($criteria -> id);
            }
        }
    }

    // Remove especialidad
    if(isset($data -> sespecs)) {
        foreach($data->sespecs as $k => $v) {
            $criteria -> type = MGM_CRITERIA_ESPECIALIDAD;
            $criteria -> value = $v;
            if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
                continue ;
            } else {
                $DB->delete_records('edicion_criterios', array('id'=> $criteriaid -> id));
            }
        }
    }

    // Tutor payment
    if(isset($data->tutorpayment)) {
        $criteria->type = MGM_CRITERIA_PAGO;
        $criteria->value = $data->tutorpayment;
        if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
            $DB->insert_record('edicion_criterios', $criteria);
        } else {
            $criteria->id = $criteriaid->id;
            $DB->update_record('edicion_criterios', $criteria);
            unset($criteria->id);
        }
    }

    // Tarea Ecuador
    if(isset($data->ecuadortask)) {
        $criteria->type = MGM_CRITERIA_ECUADOR;
        $criteria->value = $data->ecuadortask;
        if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
            $DB->insert_record('edicion_criterios', $criteria);
        } else {
            $criteria->id = $criteriaid->id;
            $DB->update_record('edicion_criterios', $criteria);
            unset($criteria->id);
        }
    }

    // Duración del curso para pago al coordinador
    if(isset($data->duration)) {
        $criteria->type = MGM_CRITERIA_DURATION;
        $criteria->value = $data->duration;
        if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
            $DB->insert_record('edicion_criterios', $criteria);
        } else {
            $criteria->id = $criteriaid->id;
            $DB->update_record('edicion_criterios', $criteria);
            unset($criteria->id);
        }
    }

    // Importe por labor previa al curso
    if(isset($data->prevlab)) {
        $criteria->type = MGM_CRITERIA_PREVLAB;
        $criteria->value = $data->prevlab;
        if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
            $DB->insert_record('edicion_criterios', $criteria);
        } else {
            $criteria->id = $criteriaid->id;
            $DB->update_record('edicion_criterios', $criteria);
            unset($criteria->id);
        }
    }

    // Tramos
    if(isset($data->tramo)) {
        $criteria->type = MGM_CRITERIA_TRAMOS;
        $criteria->value = implode(',', $data->tramo);
        if(!$criteriaid = mgm_edition_course_criteria_data_exists($criteria)) {
            $DB->insert_record('edicion_criterios', $criteria);
        } else {
            $criteria->id = $criteriaid->id;
            $DB->update_record('edicion_criterios', $criteria);
            unset($criteria->id);
        }
    }
}

function mgm_edition_course_criteria_data_exists($criteria) {
    global $CFG, $DB;

    if($criteria -> type !== MGM_CRITERIA_ESPECIALIDAD) {
        $sql = 'SELECT id FROM ' . $CFG -> prefix . 'edicion_criterios
    			WHERE edicion = \'' . $criteria -> edicion . '\' AND course = \'' . $criteria -> course . '\'
    			AND type = \'' . $criteria -> type . '\'';
        if(!$value = $DB->get_record_sql($sql)) {
            return false;
        }
    } else {
        $sql = 'SELECT id FROM ' . $CFG -> prefix . 'edicion_criterios
    		WHERE edicion = \'' . $criteria -> edicion . '\' AND course = \'' . $criteria -> course . '\'
    		AND type = \'' . $criteria -> type . '\' AND value = \'' . $criteria -> value . '\'';
        if(!$value = $DB->get_record_sql($sql)) {
            return false;
        }
    }
    return $value;
}

function mgm_get_edition_plazas($edition) {
	global $DB;
    if(!is_object($edition)) {
        return '';
    }
    $plazas = 0;
    $courses = $DB->get_records('edicion_course', array('edicionid'=> $edition -> id));
    if($courses) {
        foreach($courses as $course) {
            if($criteria = mgm_get_edition_course_criteria($edition -> id, $course -> courseid)) {
                $plazas += $criteria -> plazas;
            }
        }
    }
    return $plazas;
}

function mgm_get_course_especialidades($courseid, $editionid) {
    $criteria = mgm_get_edition_course_criteria($editionid, $courseid);
    return $criteria -> espec;
}

function mgm_get_course_available_especialidades($courseid, $editionid) {
    global $CFG, $DB;

    $data = mgm_get_course_especialidades($courseid, $editionid);
    $sql = "SELECT value FROM " . $CFG -> prefix . "edicion_ite
    		WHERE type = " . MGM_ITE_ESPECIALIDADES . "";
    $especialidades = explode("\n",  $DB->get_record_sql($sql) -> value);

    $strdata = implode(',', $data);
    $filterespecialidades = array_filter($especialidades, create_function('$element', '$data = explode(",", "' . $strdata . '"); return (!in_array($element, $data));'));
    return $filterespecialidades;
}

/**
 * Returns the active edition if any, elsewhere returns false
 *
 * @return object bool
 */
function mgm_get_active_edition() {
	global $DB;
    if(!$edition = $DB->get_record('edicion', 'active', 1)) {
        return false;
    }
    return $edition;
}

/**
 * Returns true if edition is the active one otherwise returns false
 * @param object $edition
 * @return bool
 */
function mgm_edition_is_active($edition) {
    return ($edition -> active) ? true : false;
}

/**
 * Returns true if edition is certified otherwise returns false
 *
 * @param object $edition
 * @return bool
 */
function mgm_edition_is_certified($edition) {
    if(!$edition) {
        return false;
    }
    return ($edition -> certified) ? true : false;
}

/**
 * Returns true if edition certification is on draft state otherwise returns false
 *
 * @param object $edition
 * @return boolean
 */
function mgm_edition_is_on_draft($edition) {
    return ($edition -> certified == MGM_CERTIFICATE_DRAFT) ? true : false;
}

/**
 * Returns true if edition certification is on validates state otherwise returns false
 *
 * @param object $edition
 * @return boolean
 */
function mgm_edition_is_on_validate($edition) {
    return ($edition -> certified == MGM_CERTIFICATE_VALIDATED) ? true : false;
}

/**
 * Sets the edition certification state as draft
 *
 * @param object $edition
 * @return boolean
 */
function mgm_set_edition_certification_on_draft($edition) {
    if(is_object($edition)) {
        if(!mgm_edition_is_certified($edition)) {
            $edition -> certified = MGM_CERTIFICATE_DRAFT;
            return true;
        }
    }
    return false;
}

/**
 * Sets the edition certification state as validated
 *
 * @param object $edition
 * @return boolean
 */
function mgm_set_edition_certification_on_validate($edition) {
    if(is_object($edition)) {
        if(mgm_edition_is_on_draft($edition)) {
            $edition -> certified = MGM_CERTIFICATE_VALIDATED;
            return true;
        }
    }
    return false;
}

/**
 * Return the course's edition (Edition will be active)
 *
 * @param string $id
 * @return null object The edition object
 */
function mgm_get_course_edition($id) {
	global $DB;
    if(!$row = $DB->get_record('edicion_course', 'courseid', $id)) {
        return null;
    }
    if(!$edition = $DB->get_record('edicion', 'id', $row -> edicionid)) {
        return null;
    }
    return $edition;
}

function mgm_get_edition_user_options($edition, $user) {
    global $CFG, $DB;
    $sql = "SELECT value FROM " . $CFG -> prefix . "edicion_preinscripcion
    		WHERE edicionid = '" . $edition . "' AND userid = '" . $user . "'";

    if(!$data = $DB->get_record_sql($sql)) {
        return false;
    } else {
        $data = $data -> value;
    }
    $choices = array();
    $options = explode(',', $data);
    foreach($options as $option) {
        $choices[] = $option;
    }
    return $choices;
}

function mgm_preinscribe_user_in_edition($edition, $user, $courses, $ret) {
	global $DB;
    $rcourses = array();
    foreach($courses as $course) {
        if($course) {
            $rcourses[] = $course;
        }
    }
    if(!count($rcourses)) {
        $DB->delete_records('edicion_preinscripcion', 'edicionid', $edition, 'userid', $user);
        return ;
    }
    $strcourses = implode(',', $rcourses);

    if(!$record = $DB->get_record('edicion_preinscripcion', 'edicionid', $edition, 'userid', $user)) {
        // New record
        $record = new stdClass();
        $record -> edicionid = $edition;
        $record -> userid = $user;
        $record -> value = $strcourses;
        $record -> timemodified = time();
        $DB->insert_record('edicion_preinscripcion', $record);
    } else {
        // Update record
        $record -> value = $strcourses;
        $record -> timemodified = time();
        $DB->update_record('edicion_preinscripcion', $record);
    }
}

/**
 * Inscribe an user into an edition
 * @param string $edition
 * @param string $user
 * @param string $course
 */
function mgm_inscribe_user_in_edition($edition, $user, $course, $released = false) {
    global $CFG, $DB;

    $sql = "SELECT * FROM ".$CFG->prefix."edicion_inscripcion
    		WHERE edicionid='".$edition."' AND userid='".$user."'";

    if(!$record = $DB->get_record_sql($sql)) {
        // New record
        $record = new stdClass();
        $record->edicionid = $edition;
        $record->userid = $user;
        $record->value = $course;
        $record->released = $released;
        $DB->insert_record('edicion_inscripcion', $record);
    } else {
        // Update record
        $record->edicionid = $edition;
        $record->userid = $user;
        $record->value = $course;
        $record->released = $released;
        $record->timemodified = time();
        $DB->update_record('edicion_inscripcion', $record);
    }
}

/**
 * We need to overwrite some Moodle core funcs that are not designed to be masive
 *
 * This function makes a role-assignment (a role for a user or group in a particular context)
 * @param $course - the course
 * @param $users - users
 * @param $timestart - time this assignment becomes effective
 * @param $timeend - time this assignemnt ceases to be effective
 * @uses $USER
 */
function mgm_massive_role_assign($course, $users, $timestart=0, $timeend=0, $enrol='mgm') {
    global $USER, $CFG, $DB;

    if (($timestart and $timeend) and ($timestart > $timeend)) {
        debugging('The end time can not be earlier than the start time');
        return false;
    }

    if (!$timemodified) {
        $timemodified = time();
    }

    foreach($users as $user) {
    	global $DB;
        /// Check for existing entry
        if($user->id) {
            $ra = $DB->get_record('role_assignments', 'roleid', $course->role->id, 'contextid', $course->context->id, 'userid', $user->id);
        }

        if (empty($ra)) {
            $ra = new object();
            $ra->roleid = $course->role->id;
            $ra->contextid = $course->context->id;
            $ra->userid = $user->id;
            $ra->hidden = 0;
            $ra->enrol = $enrol;
            /// Always round timestart downto 100 secs to help DBs to use their own caching algorithms
            /// by repeating queries with the same exact parameters in a 100 secs time window
            $ra->timestart = round($timestart, -2);
            $ra->timeend = $timeend;
            $ra->timemodified = $timemodified;
            $ra->modifierid = empty($USER->id) ? 0 : $USER->id;

            if (!$ra->id = $DB->insert_record('role_assignments', $ra)) {
               return false;
            }
        } else {
            $ra->id = $ra->id;
            $ra->hidden = 0;
            $ra->enrol = $enrol;
            /// Always round timestart downto 100 secs to help DBs to use their own caching algorithms
            /// by repeating queries with the same exact parameters in a 100 secs time window
            $ra->timestart = round($timestart, -2);
            $ra->timeend = $timeend;
            $ra->timemodified = $timemodified;
            $ra->modifierid = empty($USER->id) ? 0 : $USER->id;

            if (!$DB->update_record('role_assignments', $ra)) {
                return false;
            }
        }

        if (!empty($USER->id) && $USER->id == $user->id) {
            /// If the user is the current user, then do full reload of capabilities too.
            load_all_capabilities();
        }

        events_trigger('role_assigned', $ra);
    }

    /// Ask all the modules if anything needs to be done for this user
    if ($mods = get_list_of_plugins('mod')) {
        foreach ($mods as $mod) {
            include_once($CFG->dirroot.'/mod/'.$mod.'/lib.php');
            $functionname = $mod.'_role_assign';
            if (function_exists($functionname)) {
                $functionname($userid, $context, $roleid);
            }
        }
    }

    /// now handle metacourse role assignments if in course context
    if ($context->contextlevel == CONTEXT_COURSE) {
        if ($parents = $DB->get_records('course_meta', 'child_course', $context->instanceid)) {
            foreach ($parents as $parent) {
                sync_metacourse($parent->parent_course);
            }
        }
    }

    return true;
}

/**
 * Moodle core functions are not designed for massive enrolments
 * we need to create our own enrolment functions on MGM
 *
 * A convenience function to take care of the common case where you
 * just want to enrol someone using the default role into a course
 *
 * @param object $course
 * @param object $users
 * @param string $enrol - the plugin used to do this enrolment
 */
function mgm_enrol_into_course($course, $users, $enrol) {

    $timestart = time();
    // remove time part from the timestamp and keep only the date part
    $timestart = make_timestamp(date('Y', $timestart), date('m', $timestart), date('d', $timestart), 0, 0, 0);
    if ($course->enrolperiod) {
        $timeend = $timestart + $course->enrolperiod;
    } else {
        $timeend = 0;
    }

    if (!mgm_massive_role_assign($course, $users, $timestart, $timeend, $enrol)) {
        return false;
    }

    mark_context_dirty($course->context->path);
    add_to_log($course->id, 'course', 'enrol', 'view.php?id='.$course->id, $course->id);

    return true;
}

function mgm_enrol_edition_course($editionid, $courseid) {
    global $CFG, $DB;

    $sql = "SELECT userid FROM ".$CFG->prefix."edicion_inscripcion
    		WHERE edicionid='".$editionid."' AND value='".$courseid."'";
    if($data = $DB->get_records_sql($sql)) {
        $course = get_record('course', 'id', $courseid);
        if (!$role = get_default_course_role($course)) {
            error('Role doen\'t exists for course '.$course->shortname);
            die();
        }
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        $course->role = $role;
        $course->context = $context;

        $users = array();
        foreach($data as $row) {
            $usql = "SELECT auth, email, id, emailstop, deleted, mnethostid, mailformat, firstname, lastname
                     FROM ".$CFG->prefix."user
                     WHERE id='".$row->userid."'";

            if(!$user = $DB->get_record_sql($usql)) {
                continue;
            }

            $users[] = $user;
        }

        if(!mgm_enrol_into_course($course, $users, 'mgm')) {
            print_error('couldnotassignrole');
        }
    }
}

function mgm_create_enrolment_groups($editionid, $courseid) {
    global $CFG, $DB;

    if(!$inscripcion = mgm_check_already_enroled($editionid, $courseid)) {
        trigger_error('Error, there is no inscription for the edition and course ids given');
        return false;
    }

    if(!$criteria = mgm_get_edition_course_criteria($editionid, $courseid)) {
        trigger_error('Error, there is no criteria for the edition and course ids given');
        return false;
    }

    $max = round($criteria->plazas / $criteria->numgroups);

    // Split data in groups by CC
    $groups = array();
    $ncount = 0;
    $mcount = array();
    foreach($inscripcion as $row) {
        if(!array_key_exists($ncount, $groups)) {
            $groups[$ncount] = array();
        }
        $user = $DB->get_record('user', 'id', $row->id);
        if(!$user->ite_data = $DB->get_record('edicion_user', 'userid', $row->userid)) {
            if(count($groups[$ncount]) < $max) {
                $groups[$ncount][] = $user;
            } else {
                $ncount++;
                $groups[$ncount][] = $user;
            }
        } else {
            if(!$user->ite_data->cc) {
                if(count($groups[$ncount] < $max)) {
                    $groups[$ncount][] = $user;
                } else {
                    $ncount++;
                    $groups[$ncount][] = $user;
                }
            } else {
                if(array_key_exists($user->ite_data->cc, $mcount)) {
                    if(count($groups[$mcount[$user->ite_data->cc]]) < $max) {
                        $groups[$mcount[$user->ite_data->cc]][] = $user;
                    } else {
                        $mcount[$user->ite_data->cc]++;
                        $groups[$mcount[$user->ite_data->cc]][] = $user;
                    }
                } else {
                    $mcount[$user->ite_data->cc] = $user->ite_data -> cc + rand(rand(1, 10000), rand(10000, 100000));
                    $groups[$mcount[$user->ite_data->cc]][] = $user;
                }
            }
        }
    }

    $finalgroups = array();
    for($i = 0; $i < $criteria->numgroups; $i++) {
        $finalgroups[$i] = array();
        $x = 1;
        foreach($groups as $group) {
            foreach($group as $gr) {
                if(($x <= ($i + 1) * $max) && ($x > ($i * $max))) {
                    $finalgroups[$i][] = $gr;
                }
                $x++;
            }
        }
    }

    $x = 0;
    foreach($finalgroups as $fg) {
        $group = new object();
        $group->courseid = $courseid;
        $group->name = groups_parse_name('Grupo @', $x);

        if(!$gid = groups_create_group(addslashes_recursive($group))) {
            error('Error creating the ' . $group->name . ' group');
        }
        foreach($fg as $user) {
            if(!groups_add_member($gid, $user->id)) {
                error('Error adding user ' . $user->username . ' to group ' . $group -> name);
            }
        }
        $x++;
    }

    return true;
}

function mgm_check_already_enroled($editionid, $courseid) {
    global $CFG, $DB;

    $sql = "SELECT userid as id FROM " . $CFG -> prefix . "edicion_inscripcion
    		WHERE edicionid='" . $editionid . "' AND value='" . $courseid . "'";
    return $DB->get_records_sql($sql);
}

function mgm_edition_get_solicitudes($edition, $course) {
    global $CFG, $DB;

    $ret = 0;
    $sql = "SELECT count(id) as count FROM " . $CFG -> prefix . "edicion_preinscripcion
    		WHERE edicionid='" . $edition -> id . "'
    		AND value IN (".$course->id.")";
    if($record = $DB->get_record_sql($sql)) {
       return $record->count;
    }
    return $ret;
}

/**
 * Return true if the given course of given edition has criteria set
 * @param object $edition
 * @param object $course
 * @return boolean
 */
function mgm_exists_criteria_for_course($edition, $course) {
    // Local variables
    $ret = false;
    $criteria = mgm_get_edition_course_criteria($edition -> id, $course -> id);

    if(isset($criteria -> opcion1)) {
        $ret = $criteria -> opcion1 . ', ' . $criteria -> opcion2;
    }

    return $ret;
}

/**
 * Return the user preinscription data as array
 * @param object $line
 * @return array
 */
function mgm_get_user_preinscription_data($line, $edition, $data, $criteria, $courseenrolid=false) {
    global $CFG;

    $site = get_site();
    $user = $data -> user;
    $especs = explode("\n",ltrim($data -> user -> especialidades,"\n"));
    $userespecs = '<select name="especialidades" readonly="">';
    foreach($especs as $espec) {
        $userespecs .= '<option name="' . $espec . '">' . mgm_translate_especialidad($espec) . '</option>';
    }
    $userespecs .= '</select>';
    $courses = '<select name="courses" readonly="">';
    $values = explode(',', $line -> value);
    $realcourses = array();
    for($i = 0; $i < count($values); $i++) {
        if(mgm_check_already_enroled($edition -> id, $values[$i])) {
            continue ;
        }
        $realcourses[] = $values[$i];
    }

    foreach($values as $courseid) {
        $ncourse = get_record('course', 'id', $courseid);
        $courses .= '<option name="' . $courseid . '">' . $ncourse -> fullname . '</option>';
    }
    $courses .= '</select>';
    $check = '<input type="checkbox" name="users[' . $line -> userid . ']" />';
    $colors='';
    $state='<input type="hidden" name="state[' . $line -> userid . ']" value="1" />';
    $cc_type = mgm_get_cc_type($data->user->cc, true);
    //Comprobar centro privado (rojo)
    if($cc_type == MGM_PRIVATE_CENTER || $cc_type == -1) {
        $colors = '<span style="color: red;">(*) </span>';
        $check = '<input type="checkbox" name="users[' . $line -> userid . ']" checked="false" />';
        $state='<input type="hidden" name="state[' . $line -> userid . ']" value="2" />';
    }
    //Comprobar si el usuario ha certificado el curso (Amarillo)
    if ($courseenrolid){
			$ch=mgm_check_cert_history($line->userid, array($courseenrolid));
			if (! $ch[0]){//El usuario ya tiene el curso certificado
				      $colors = $colors.'<span style="color: yellow;">(*)</span> ';
				      $check = '<input type="checkbox" name="users[' . $line -> userid . ']" checked="false" />';
				      $state='<input type="hidden" name="state[' . $line -> userid . ']" value="3" />';
			}
    }
    //Comprobar comunidad autonoma (Naranja)
    if($criteria -> comunidad) {
        $provincia = str_split($data->user->cc, 2);
        if(mgm_is_ca($provincia, $criteria->comunidad)) {
            $colors = $colors. '<span style="color: orange;">(*)</span> ';
            $check = '<input type="checkbox" name="users[' . $line -> userid . ']" checked="false" />';
            $state='<input type="hidden" name="state[' . $line -> userid . ']" value="4" />';
        }
    }
		$name=$colors.'<a href="../../user/view.php?id=' . $line -> userid . '&amp;course=' . $site -> id . '">' . $user -> firstname . '</a>';
    $tmpdata = array($check, $name.$state, $user->lastname, date("d/m/Y H:i\"s", $line -> timemodified), ($data -> user -> cc) ? $data -> user -> cc : '', ($data -> user -> cc) ? mgm_get_ccaa($data -> user -> cc) : '' , $userespecs, $courses);

    return $tmpdata;
}

function mgm_is_ca($pr, $cm) {
    global $PROV_COMUNIDADES;

    foreach($PROV_COMUNIDADES as $k => $v) {
        foreach($v as $k2 => $v2) {
            if($k == $cm) {
                if($k2 == abs($pr[0])) {
                    return true;
                }
            }
        }
    }

    return false;
}

/**
 * Reorder by course
 * @param array $file
 * @param object $course
 */
function mgm_order_by_course_preinscription_data(&$data, $course) {
    // Local variables
    $_data = array();

    if(!isset($data)) {
        return $_data;
    }

    foreach($data as $line) {
        foreach($line->realcourses as $k => $v) {
            if($course -> id == $v) {
                if(!isset($_data[$k])) {
                    $_data[$k] = array();
                }

                $_data[$k][] = $line;
            }
        }
    }

    $data = $_data;
}

/**
 * Return not already enroled courses
 * @param string $editionid
 * @param string $value
 */
function mgm_get_user_preinscription_realcourses($editionid, $value) {
    $values = explode(',', $value);
    $realcourses = array();
    for($i = 0; $i < count($values); $i++) {
        if(mgm_check_already_enroled($editionid, $values[$i])) {
            continue ;
        }
        $realcourses[] = $values[$i];
    }

    return $realcourses;
}

function mgm_user_preinscription_tmpdata($userid) {
    global $CFG, $DB;

    $sql = "SELECT u.firstname, u.lastname, eu.especialidades, eu.cc FROM " . $CFG -> prefix . "user AS u
            LEFT JOIN " . $CFG -> prefix . "edicion_user AS eu ON eu.userid=u.id WHERE u.id='" . $userid . "'";
    if(!$user = $DB->get_record_sql($sql)) {
        return null;
    }
    $tmpuser = new stdClass();
    $tmpuser -> user = mgm_prepare_user_extend($user);
    return $tmpuser;
}

function mgm_parse_preinscription_data($edition, $course, $data) {
    // Local variables
    $criteria = mgm_get_edition_course_criteria($edition -> id, $course -> id);
    $retdata = array();

    foreach($data as $sqline) {
        $lineuser = mgm_user_preinscription_tmpdata($sqline -> userid);
        $lineuser -> realcourses = mgm_get_user_preinscription_realcourses($edition -> id, $sqline -> value);
        if(empty($lineuser -> realcourses)) {
            $lineuser -> realcourses[0] = '';
        }
        $lineuser -> tmpdata = mgm_get_user_preinscription_data($sqline, $edition, $lineuser, $criteria, $course->id);
        $lineuser->especs = explode("\n",ltrim($lineuser -> user -> especialidades,"\n"));
        $lineuser -> sqline = $sqline;
        $lineuser -> data = array('opcion1' => array('especialidades' => array('found' => false, 'data' => array()), 'centros' => array('found' => false, 'data' => array('found' => array(), 'notfound' => array()))), 'opcion2' => array('especialidades' => array('found' => false, 'data' => array()), 'centros' => array('found' => false, 'data' => array('found' => array(), 'notfound' => array()))));

        // Store data by course at first option or not
        if($course -> id == $lineuser -> realcourses[0]) {
            $retdata['first'][] = $lineuser;
        } else {
            $retdata['last'][] = $lineuser;
        }
    }

    /*
     * Now we have all the data splited into those ones who choosed the course as first option at
     * $first and everyone else at $last. We are going to proccess and order the data on every one
     * of those arrays before return it.
     */
    $rlastdata = array();
    if(isset($criteria -> opcion1)) {    // This course is configured with criteria options
        mgm_order_preinscription_first_data($retdata['first'], $criteria, $edition, $course);
        mgm_order_by_course_preinscription_data($retdata['last'], $course);
        if(isset($retdata['last'])) {
            foreach($retdata['last'] as $rdata) {
                mgm_order_preinscription_last_data($rdata, $edition, $criteria, $course);
                foreach($rdata as $rd) {
                    $rlastdata[] = $rd;
                }
            }
        }
    } else {    // This course is not configured with criteria options
        foreach($retdata as $key => $value) {
            if($key == 'first') {
                foreach($value as $k => $v) {
                    $retdata[$key][$k] = $v -> tmpdata;
                }
            } else {
                foreach($value as $k => $v) {
                    $rlastdata[$k] = $v -> tmpdata;
                }
            }
        }
    }

    $tmpdata = array();
    if(isset($retdata['first'])) {
        foreach($retdata['first'] as $data) {
            $tmpdata[] = $data;
        }
    }

    foreach($rlastdata as $data) {
        $tmpdata[] = $data;
    }

    return $tmpdata;
}

function mgm_order_preinscription_first_data_opcion($opcion, &$data, $criteria, $edition, $course) {
    if(!isset($data)) {
        return ;
    }
    foreach($data as $linedata) {
        if($criteria -> $opcion == 'especialidades') {
            // First option is especialidades
            $linedata -> data[$opcion]['especialidades']['found'] = true;
            $kets = array_keys($criteria -> espec);
            $found = false;
            // Each espec on course criteria
            foreach($kets as $key => $value) {
                // Each espec on user criteria
                foreach($linedata->especs as $k => $v) {
                    // Match
                    if($v !== '' && $value == $v) {
                        $linedata -> data[$opcion]['especialidades']['data'][$key][$k] = $linedata;
                        $found = true;
                    }
                }
            }
            // If criteria does not match just store it in date order
            if(!$found) {
                $linedata -> data[$opcion]['especialidades']['data']['notfound'][] = $linedata;
            }
        } else if($criteria -> $opcion == 'centros') {
            // First option is centros
            $linedata -> data[$opcion]['centros']['found'] = true;

            if(!empty($linedata -> user) && mgm_is_cc_on_mec($linedata->user->cc)) {
                $linedata -> data[$opcion]['centros']['data']['found'] = $linedata;
            } else {
                $linedata -> data[$opcion]['centros']['data']['notfound'] = $linedata;
            }
        } else if($criteria -> $opcion == 'ninguna') {
            $linedata -> data[$opcion]['ninguna']['found'] = true;
            $linedata -> data[$opcion]['ninguna']['data'] = $linedata;
        }
    }
}

function mgm_abstract_results_opcion($opcion, $data) {
    // Local variables
    $tmptdata = array();

    if(!isset($data)) {
        return $tmptdata;
    }

    foreach($data as $linedata) {
        // If especialidades is the first option
        if($linedata -> data[$opcion]['especialidades']['found']) {
            foreach($linedata->data[$opcion]['especialidades']['data'] as $key => $value) {
                foreach($value as $k => $v) {
                    $vdata = new stdClass();
                    $vdata -> sqline = $v -> sqline;
                    $vdata -> data = $v -> tmpdata;
                    if($key !== 'notfound') {
                        $tmptdata['found'][$k][] = $vdata;
                    } else {
                        $tmptdata['notfound'][] = $v;
                    }
                }
            }
        } else if($linedata -> data[$opcion]['centros']['found']) {
            foreach($linedata->data[$opcion]['centros']['data'] as $key => $value) {
                if(empty($value))
                    continue ;
                $vdata = new stdClass();
                $vdata -> sqline = $value -> sqline;
                $vdata -> data = $value -> tmpdata;
                if($key !== 'notfound') {
                    $tmptdata[$key][0][] = $vdata;
                } else {
                    $tmptdata[$key][] = $value;
                }
            }
        } else if($linedata -> data[$opcion]['ninguna']['found']) {
            $vdata = new stdClass();
            $vdata -> sqline = $linedata->data[$opcion]['ninguna']['data']->sqline;
            $vdata -> data = $linedata->data[$opcion]['ninguna']['data']->tmpdata;
            $tmptdata['nocriteria'][0][] = $vdata;
        }
    }

    return $tmptdata;
}

function mgm_order_preinscription_first_data(&$data, $criteria, $edition, $course) {
    // Local variables
    $firstdata = $tmptdata = $founddata = $nfdata = $nffounddata = $nfnfdata = $ncdata = $finaldata = array();

    // First pass
    mgm_order_preinscription_first_data_opcion('opcion1', $data, $criteria, $edition, $course);

    // Abstract the opcion1 results
    $tmptdata = mgm_abstract_results_opcion('opcion1', $data);

    $founddata = (array_key_exists('found', $tmptdata)) ? $tmptdata['found'] : array();

    // Seccond pass (Only for not found)
    if(isset($tmptdata['notfound'])) {
        mgm_order_preinscription_first_data_opcion('opcion2', $tmptdata['notfound'], $criteria, $edition, $course);
        $nfdata = mgm_abstract_results_opcion('opcion2', $tmptdata['notfound']);
        if(array_key_exists('found', $nfdata)) {
            foreach($nfdata['found'] as $nff) {
                $nffounddata[] = $nff;
            }

            // Order data by date
            foreach($nffounddata as $nff) {
                usort($nff, 'mgm_order_by_date');
            }
        }

        if(array_key_exists('notfound', $nfdata)) {
            foreach($nfdata['notfound'] as $nfnf) {
                $nfnfdata[] = $nfnf;
            }

            // Order data by date
            usort($nfnfdata, 'mgm_order_by_date');
        }

        if(array_key_exists('nocriteria', $nfdata)) {
            foreach($nfdata['nocriteria'] as $ncd) {
                $ncdata[] = $ncd;
            }

            // Order data by date
            foreach($ncdata as $ncd) {
                usort($ncd, 'mgm_order_by_date');
            }
        }
    }

    if(array_key_exists('nocriteria', $tmptdata)) {
        $ncdata = $tmptdata['nocriteria'];
    }

    // Order data by date
    foreach($founddata as $found) {
        usort($found, 'mgm_order_by_date');
    }

    /**
     * Create tha final and valid array
     */
    foreach($founddata as $found) {
        foreach($found as $finalfound) {
            $finaldata[] = $finalfound -> data;
        }
    }

    if(!empty($nffounddata)) {
        foreach($nffounddata as $nffound) {
            foreach($nffound as $nff) {
                $finaldata[] = $nff -> data;
            }
        }
    }

    if(!empty($nfnfdata)) {
        foreach($nfnfdata as $nfnf) {
            $finaldata[] = $nfnf -> tmpdata;
        }
    }

    if(!empty($ncdata)) {
        foreach($ncdata as $ncd) {
            foreach($ncd as $nc) {
                $finaldata[] = $nc -> data;
            }
        }
    }

    $data = $finaldata;
}

function mgm_order_preinscription_last_data(&$data, $edition, $criteria, $course) {
    // Local variables
    $firstdata = $tmptdata = $founddata = $nfdata = $nffounddata = $nfnfdata = $ncdata = $finaldata = array();

    // First pass
    mgm_order_preinscription_first_data_opcion('opcion1', $data, $criteria, $edition, $course);

    // Abstract the opcion1 results
    $tmptdata = mgm_abstract_results_opcion('opcion1', $data);

    $founddata = (array_key_exists('found', $tmptdata)) ? $tmptdata['found'] : array();

    // Seccond pass (Only for not found)
    if(isset($tmptdata['notfound'])) {
        mgm_order_preinscription_first_data_opcion('opcion2', $tmptdata['notfound'], $criteria, $edition, $course);
        $nfdata = mgm_abstract_results_opcion('opcion2', $tmptdata['notfound']);
        if(array_key_exists('found', $nfdata)) {
            foreach($nfdata['found'] as $nff) {
                $nffounddata[] = $nff;
            }

            // Order data by date
            foreach($nffounddata as $nff) {
                usort($nff, 'mgm_order_by_date');
            }
        }

        if(array_key_exists('notfound', $nfdata)) {
            foreach($nfdata['notfound'] as $nfnf) {
                $nfnfdata[] = $nfnf;
            }

            // Order data by date
            usort($nfnfdata, 'mgm_order_by_date');
        }

        if(array_key_exists('nocriteria', $nfdata)) {
            foreach($nfdata['nocriteria'] as $ncd) {
                $ncdata[] = $ncd;
            }

            // Order data by date
            foreach($ncdata as $ncd) {
                usort($ncd, 'mgm_order_by_date');
            }
        }
    }

    if(array_key_exists('nocriteria', $tmptdata)) {
        $ncdata = $tmptdata['nocriteria'];
    }

    // Order data by date
    foreach($founddata as $found) {
        usort($found, 'mgm_order_by_date');
    }

    /**
     * Create tha final and valid array
     */
    foreach($founddata as $found) {
        foreach($found as $finalfound) {
            $finaldata[] = $finalfound -> data;
        }
    }

    if(!empty($nffounddata)) {
        foreach($nffounddata as $nffound) {
            foreach($nffound as $nff) {
                $finaldata[] = $nff -> data;
            }
        }
    }

    if(!empty($nfnfdata)) {
        foreach($nfnfdata as $nfnf) {
            $finaldata[] = $nfnf -> tmpdata;
        }
    }

    if(!empty($ncdata)) {
        foreach($ncdata as $ncd) {
            foreach($ncd as $nc) {
                $finaldata[] = $nc -> data;
            }
        }
    }

    $data = $finaldata;
}

function mgm_order_by_date($x, $y) {
    if($x -> sqline -> timemodified == $y -> sqline -> timemodified) {
        return 0;
    }
    return ($x -> sqline -> timemodified < $y -> sqline -> timemodified) ? -1 : 1;
}

/**
 * Get edition inscription data and return it
 *
 * @param object $edition
 * @param object $course
 * @param boolean $docheck
 */
function mgm_get_edition_course_inscription_data($edition, $course, $docheck = true) {
    global $CFG, $DB;

    // Inscription data
    $sql = "SELECT * FROM ".$CFG->prefix."edicion_inscripcion
    		WHERE edicionid = '".$edition->id."' AND value='".$course->id."'
    		ORDER BY timemodified ASC";
    if(!$inscripcion = $DB->get_records_sql($sql)) {
        return ;
    }

    $data = array();
    foreach($inscripcion as $line) {
        $tmpdata = mgm_user_preinscription_tmpdata($line->userid);
        $data[] = array('<a href="../../user/view.php?id='.$line->userid.'&amp;course=1">'.$tmpdata->user->firstname.'</a>', $tmpdata->user->lastname.'<input type="hidden" name="users['.$line->userid.']" value="1"></input>');
    }

    return $data;
}

/**
 * Get edition preinscrition data and return it
 *
 * @param object $edition
 * @param object $course
 * @param boolean $docheck
 */
function mgm_get_edition_course_preinscripcion_data($edition, $course, $docheck = true) {
    global $CFG, $DB;

    // Preinscripcion date first
    $sql = "SELECT * FROM ".$CFG->prefix."edicion_preinscripcion
    		WHERE edicionid = '".$edition->id."' AND
    		userid NOT IN (SELECT userid FROM ".$CFG->prefix."edicion_inscripcion
    		WHERE edicionid='".$edition->id."' )
    		AND value REGEXP '^".$course->id."$|^".$course->id.",|,".$course->id.",|,".$course->id."$' ORDER BY timemodified ASC";

    if(!$preinscripcion = $DB->get_records_sql($sql)) {
        return ;
    }
     $sql="select distinct value from ".$CFG->prefix."edicion_inscripcion where edicionid=".$edition->id;
     	$course_with_inscription = $DB->get_records_sql($sql);
    	if ($course_with_inscription){
    		$course_with_inscription=array_keys($course_with_inscription);
    	}else{
    		$course_with_inscription=array();
    	}
	    foreach ($preinscripcion as $i=>$u){
	    if ($a = $DB->get_record('edicion_descartes', 'edicionid', $edition->id,'userid', $u->userid, 'code', 4 )){//Eliminamos usuarios que han sido descartados por comunidad en algun otro curso de la edicion
							unset($preinscripcion[$i]);
							continue;
				  }else{
	    	  $cursos=explode(',', $u->value);
						foreach ($cursos as $cid){//eliminamos usuarios con opciones prioritarias aun no asignadas
							if ($cid != $course->id){
								if(array_search($cid, $course_with_inscription) === FALSE){
								  unset($preinscripcion[$i]);
								  break;
								}
							}else{
								break;
							}
						}
				  }
	    }


    $data = mgm_parse_preinscription_data($edition, $course, $preinscripcion);
    if($docheck) {
        $criteria = mgm_get_edition_course_criteria($edition -> id, $course -> id);
        $asigned = 0;
        foreach($data as $k => $row) {
            $arr = explode('"', $row[0]);
            $userid = $arr[3];
            if($arr[5]=="false"){
            	$data[$k][0] = '<input type="checkbox" name="' . $userid . '" />';
              continue;
            }
            $check = '<input type="checkbox" name="' . $userid . '" checked="true"/>';
            if($criteria -> plazas > $asigned || $criteria -> plazas == 0) {
                $data[$k][0] = $check;
                $asigned++;
            }
        }
    }

    $count = 1;
    foreach($data as $k => $row) {
        $r = $row;
        array_unshift($r, $count);
        $data[$k] = $r;
        $count++;
    }

    return $data;
}

function mgm_prepare_user_extend($euser){
  if (isset($euser->especialidades)){
    $euser->especialidades=ltrim($euser->especialidades, "\n");
  }
  if (isset($euser->cc) && strlen($euser->cc)<8){
   	while( strlen($euser->cc)<8){
   		$euser->cc='0'.$euser->cc;
   	}
  }
  return $euser;
}

/**
 * Return the user's extended data
 * @param string $userid
 * @return string
 */
function mgm_get_user_extend($userid) {
	global $DB;
    if($euser = $DB->get_record('edicion_user', 'userid', $userid)) {
        return mgm_prepare_user_extend($euser);
    }

    $euser = new stdClass();
    $euser -> cc = '';
    $euser -> dni = '';
    $euser -> tipoid = '';
    $euser -> codniveleducativo = '';
    $euser -> codcuerpodocente = '';
    $euser -> codpostal = '';
    $euser -> sexo = '';
    return $euser;
}

/**
 * Return the user's complete data
 * @param string $userid
 * @return user object which all field values
 */
function mgm_get_user_complete($userid) {
	global $DB;
	$user=false;
	if($user = $DB->get_record('user', 'id', $userid)) {
    	if($euser = $DB->get_record('edicion_user', 'userid', $userid)) { //base data
      		unset($euser->id);
        	foreach(array_keys(get_object_vars($euser)) as $key){ //mgm user data
        		$user->$key = $euser->$key;
        	}
      	}//mgm user data end
      	if ($puser = profile_user_record($userid)){//profile user data
       		unset($puser->id);
       		foreach(array_keys(get_object_vars($puser)) as $key){
       			if (! array_key_exists($key, $user)){
       				$user->$key = $puser->$key;
       			}else{
       				$key2="profile_".$key;
       				$user->$key2 = $puser->$key;
       			}
       		}
      	}//profile user data end
	}
    return $user;
}

/**
 * Set the user's complete data
 * @param user object
 * @return bool
 */
function mgm_update_user_complete($user) {
	global $DB;
    if( $DB->get_record('user', 'id', $user->id)) {
    	$DB->update_record('user', $user);//user base data save
			profile_save_data($user);// profile data save
    	if ($userext = $DB->get_record('edicion_user', 'userid', $user->id)) {// edicion_user data save
      		$user -> userid = $user->id;
        	$user -> id = $userext->id;
        	$DB->update_record('edicion_user', $user);
    	}else{
        	$user->userid = $user->id;
        	unset($user->id);
        	$DB->insert_record('edicion_user', $user);
    	}
      	return true;
    }
    return false;
}
function mgm_get_user_especialidades($userid) {
	global $DB;
    if($especialidades = $DB->get_record('edicion_user', 'userid', $userid)) {
        $especs = array();
        foreach(explode("\n", $especialidades->especialidades) as $espec) {
            $especs[$espec] = mgm_translate_especialidad($espec);
        }
        return $especs;
    }
    return array();
}

function mgm_get_user_available_especialidades($userid, $codcuerpodocente=false) {
    global $CFG, $DB;

    $data = mgm_get_user_especialidades($userid);
    $sql = "SELECT value FROM " . $CFG -> prefix . "edicion_ite
    		WHERE type = " . MGM_ITE_ESPECIALIDADES . "";
    $especialidades = explode("\n",  $DB->get_record_sql($sql) -> value);
    $strdata = implode(',', $data);
    $filterespecialidades = array_filter($especialidades, create_function('$element', '$data = explode(",", "' . $strdata . '"); return (!in_array($element, $data));'));
    if ($codcuerpodocente){
    	global $MGM_ITE_CUERPOS_ESPECS;
    	$a=array_intersect_key($filterespecialidades, array_flip($MGM_ITE_CUERPOS_ESPECS[$codcuerpodocente]));
    	asort($a, SORT_STRING);
    	return $a;
    }
    asort($filterespecialidades, SORT_STRING);
    return $filterespecialidades;
}
function mgm_get_all_especialidades($codcuerpodocente=false) {
	global $CFG, $DB;
	$sql = "SELECT value FROM " . $CFG -> prefix . "edicion_ite
    		WHERE type = " . MGM_ITE_ESPECIALIDADES . "";
    $especialidades = explode("\n",  $DB->get_record_sql($sql) -> value);
    if ($codcuerpodocente){
    	global $MGM_ITE_CUERPOS_ESPECS;
    	$a=array_intersect_key($especialidades, array_flip($MGM_ITE_CUERPOS_ESPECS[$codcuerpodocente]));
    	asort($a, SORT_STRING );
    	return $a;
    }
    asort($especialidades, SORT_STRING);
    return $especialidades;
}


/**
 * Check if the user cc is a valid cc
 * @param string $code
 * @param string $ret
 * @return string
 */
function mgm_check_user_cc($code, &$ret) {
    if(!mgm_is_cc_on_db($code)) {
        $ret = MGM_DATA_CC_ERROR;
        return '';
    }
    return $code;
}

/**
 * Check if the user dni is a valid dni
 * @param string $dni
 * @param string $ret
 * @return string
 */
function mgm_check_user_dni($userid, $dni, &$ret) {
    global $CFG, $DB;
    $sql = "SELECT * FROM " . $CFG -> prefix . "edicion_user
    		WHERE dni='" . mysql_escape_string($dni) . "' AND userid!='" . $userid . "'";
    if($odni = $DB->get_record_sql($sql)) {
        $ret = MGM_DATA_DNI_ERROR;
        return '';
    }
    if(!mgm_validate_cif($dni)) {
        $ret = MGM_DATA_DNI_INVALID;
        return '';
    }
    return $dni;
}

function mgm_set_userdata($userid, $data, $create=true) {
	global $DB;
    $ret = MGM_DATA_NO_ERROR;
    $newdata = $data;
    $newdata -> cc = mgm_check_user_cc($data -> cc, $ret);
    if($data -> tipoid == 'N') {
        $newdata -> dni = mgm_check_user_dni($userid, $data -> dni, $ret);
    } else {
        $newdata -> dni = $data -> dni;
    }
    $newdata -> userid = $userid;
    if ($create){
	    if(!$DB->record_exists('edicion_user', 'userid', $userid)) {
	        if(isset($data -> addsel)) {
	            $newdata -> especialidades = implode("\n", $data -> aespecs);
	        } else {
	            $newdata -> espcialidades = "";
	        }
	        $DB->insert_record('edicion_user', $newdata);
	    } else {
	        $olddata = $DB->get_record('edicion_user', 'userid', $userid);
	        $newdata -> id = $olddata -> id;
	        if(isset($data -> addsel)) {
	            $oldespec = explode("\n", $olddata -> especialidades);
	            $newespec = array_merge($oldespec, $data -> aespecs);
	            $newdata -> especialidades = implode("\n", $newespec);
	        } else if(isset($data -> removesel)) {
	            $oldespec = explode("\n", $olddata -> especialidades);
	            foreach($data->sespecs as $k => $v) {
	                if(in_array($v, $oldespec)) {
	                    $idx = array_search($v, $oldespec);
	                    unset($oldespec[array_search($v, $oldespec)]);
	                }
	            }
	            $newespec = implode("\n", $oldespec);
	            $newdata -> especialidades = $newespec;
	        } else {
	            $newdata -> especialidades = $olddata -> especialidades;
	        }
	        $DB->update_record('edicion_user', $newdata);
	    }
    }
    return $ret;
}

function mgm_set_userdata2($userid, $data, $create=true) {
	global $DB;
    $ret = MGM_DATA_NO_ERROR;
    $newdata = $data;
    $newdata -> cc = mgm_check_user_cc($data -> cc, $ret);
    if($data -> tipoid == 'N') {
        $newdata -> dni = mgm_check_user_dni($userid, $data -> dni, $ret);
    } else {
        $newdata -> dni = $data -> dni;
    }
    $newdata -> userid = $userid;
    if ($create){
	    if(!$rec = $DB->get_record('edicion_user', 'userid', $userid)) {
	        $DB->insert_record('edicion_user', $newdata);
	    } else {
	    	$newdata->id=$rec->id;
	        $DB->update_record('edicion_user', $newdata);
	    }
    }
    return $ret;
}

/**
 * Return if a given cc is in MEC_PATTERN
 * @param string $cc
 * @return boolean
 */
function mgm_get_ccaa($cc) {
  global $COMUNIDADES_CC;
	while( strlen($cc)<8){
   		$cc='0'.$cc;
  }
  $key=substr($cc, 0, 2);
  if (array_key_exists ( $key , $COMUNIDADES_CC )){
  	return $COMUNIDADES_CC[$key];
  }
	return '';
}




/**
 * Return if a given cc is in MEC_PATTERN
 * @param string $cc
 * @return boolean
 */
function mgm_is_cc_on_mec($cc) {
	  global $MEC_PATTERN;
		while( strlen($cc)<8){
   		$cc='0'.$cc;
   	}
    if(preg_match($MEC_PATTERN, $cc)) {
        return $cc;
    }
    return false;
}


/**
 * Return if a given cc is on cc CSV file
 * @param string $cc
 * @return boolean
 */
function mgm_is_cc_on_csv($cc) {
    $ccdata = mgm_get_cc_data();
    if(array_key_exists($cc, $ccdata)) {
        return $ccdata[$cc];
    }

    return false;
}
function mgm_is_cc_on_db($cc) {
	global $DB;
	if($ccdata = $DB->get_record('edicion_centro','codigo', $cc)) {
    	return $cc;
  	}
  	return false;
}

function mgm_get_cc_type($cc, $sql=false) {
	global $DB;
    if(!$cc) {
        return -1;
    }
    if($ccdata = $DB->get_record('edicion_centro','codigo', $cc)) {
    	return $ccdata->tipo;
    }
    return -1;
}

/**
 * Checks if a gicen cc is valid or not
 * @param string $cc
 * @return boolean
 */
function mgm_is_cc_valid($cc) {
	global $DB;
    if($ccdata = $DB->get_record('edicion_centro','codigo', $cc)) {
        if($ccdata->tipo != MGM_PRIVATE_CENTER) {
            return true;
        }
    }
	return false;
}

/**
 * Return an array with the cc data on CSV file
 * @return array
 */
function mgm_get_cc_data() {
    global $CFG, $CSV_CACHED_FILE;
    if(empty($CSV_CACHED_FILE)) {
        $csvdata = array();
        if(($gestor = fopen($CFG -> mgm_centros_file, "r")) !== FALSE) {
            while(($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                $csvdata[$datos[5]] = $datos;
            }
            fclose($gestor);
        }
        $CSV_CACHED_FILE = $csvdata;

        return $csvdata;
    } else {
        return $CSV_CACHED_FILE;
    }
}

/**
 * Activate an edition
 * @param object $edition
 */
function mgm_active_edition($edition) {
	global $DB;
    if($aedition = mgm_get_active_edition()) {
        mgm_deactive_edition($aedition);
    }

    $edition -> active = 1;
    $DB->update_record('edicion', $edition);
}

/**
 * Deactivate an edition
 * @param unknown_type $edition
 */
function mgm_deactive_edition($edition) {
	global $DB;
    $edition -> active = 0;
    $DB->update_record('edicion', $edition);
}

/**
 * Check if exist another edtition in preinscripcion state
 * @param int $editionid
 * @return bool true if not exits another
 *
 */
function mgm_edition_check_uni_state($editionid, $state){	
  global $CFG, $DB;
	$sql = "SELECT * FROM " . $CFG -> prefix . "edicion WHERE state='".$state."' AND id!=" . $editionid;
  if($reg = $DB->get_record_sql($sql)) {
  	return false;
  }
  return true;
}

function mgm_edition_get_cursos_fin($editionid){
  global $CFG, $DB;
	$sql = "select fechafin from ".$CFG->prefix."edicion_course where edicionid=".$editionid." and fechafin is not null order by fechafin desc";
  if($reg = $DB->get_record_sql($sql)) {
  	return $reg->fechafin;
  }
  return false;
}

function mgm_edition_get_cursos_inicio($editionid){
  global $CFG, $DB;
	$sql = "select fechainicio from ".$CFG->prefix."edicion_course where edicionid=".$editionid." and fechainicio is not null order by fechainicio asc";
  if($reg = $DB->get_record_sql($sql)) {
  	return $reg->fechainicio;
  }
  return false;
}

function mgm_edition_check_state($editionid, $newstate) {
	global $CFG, $DB;
	if ($edition = $DB->get_record('edicion', array('id'=> $editionid))){
		$oldstate=$edition->state;
		switch ($newstate){
			case 'borrador':
				if ($oldstate=='finalizada')
					return MGM_STATE_BI_ERROR;
				break;
			case 'preinscripcion':
				if (! $edition->active )
					return MGM_STATE_PA_ERROR;
				if (! mgm_edition_check_uni_state($editionid, 'preinscripcion'))
					return MGM_STATE_PM_ERROR;
				break;
			case 'matriculacion':
		     	if (time() < $edition->fin) {
		     		return MGM_STATE_MN_ERROR;
        		}
        		if (! $edition->active )
					return MGM_STATE_MA_ERROR;
				if (! mgm_edition_check_uni_state($editionid,'matriculacion'))
					return MGM_STATE_MM_ERROR;

				break;
			case 'en curso':
				//Comprobar periodo (entre menor inicio de todos los cursos y mayor fin todos los cursos)
				$fechainicio=mgm_edition_get_cursos_inicio($edition->id);
				$fechafin=mgm_edition_get_cursos_fin($edition->id);
				$actual=time();
				if ( (! $fechainicio) || $actual < $fechainicio || (! $fechafin) || $actual>$fechafin)
					return MGM_STATE_CF_ERROR;
				break;
			case 'gestion':
				//comprobar que han finalizado todos los cursos
				$fecha=mgm_edition_get_cursos_fin($edition->id);
				if ($fecha && time() < $fecha)
					return MGM_STATE_GE_ERROR;
				break;
			case 'finalizada':
				if ($oldstate != 'gestion'){
					return MGM_STATE_FI_ERROR;
				}
				if (!$edition->certified ){
					return MGM_STATE_FC_ERROR;
				}
				if (!$edition->paid ){
					return MGM_STATE_FP_ERROR;
				}
				break;
			default:
				print 'estado incorrecto!';
				break;
		}
	}else{
		if ($newstate!='borrador')
  		return MGM_STATE_BN_ERROR;
	}
}


/**
 * Get preinscription uer timemodified data
 * @param string $edition
 * @param string $user
 */
function mgm_get_preinscription_timemodified($edition, $user) {
    global $CFG, $DB;

    $sql = "SELECT timemodified FROM " . $CFG -> prefix . "edicion_preinscripcion
    		WHERE edicionid = '" . $edition . "' AND userid = '" . $user . "'";

    if(!$record = $DB->get_record_sql($sql)) {
        return null;
    }
    return $record;
}

function mgm_is_borrador($edition, $course) {
    global $CFG, $DB;

    $sql = "SELECT * FROM " . $CFG -> prefix . "edicion_inscripcion
            WHERE edicionid='" . $edition -> id . "' AND value='" . $course -> id . "' AND released='0'";
    if($borrador = $DB->get_records_sql($sql)) {
        return true;
    }
    return false;
}

function mgm_is_inscription_active($id, $course) {

}

function mgm_rollback_borrador($editionid, $courseid) {
    global $CFG, $DB;

    $sql = "DELETE FROM " . $CFG -> prefix . "edicion_inscripcion
    		WHERE edicionid='" . $editionid . "' AND value='" . $courseid . "'
    		AND released='0'";

    $DB->execute_sql($sql);
    //eliminar los descartes
    $DB->delete_records('edicion_descartes', 'edicionid', $editionid, 'courseid', $courseid);
}

function mgm_edition_set_user_address($userid, $address) {
	global $DB;
    if(!$euser = $DB->get_record('edicion_user', 'userid', $userid)) {
        error('User doesn\'t exists!');
    } else {
        if(!empty($addres)) {
            $euser -> alt_address = true;
            $euser -> address = $address;
        } else {
            $euser -> alt_address = false;
            $euser -> address = '';
        }
    }
}

function mgm_get_edition_out($edition) {
    global $CFG, $DB;

    $sql = "SELECT COUNT(id) AS count FROM " . $CFG -> prefix . "edicion_preinscripcion
    		WHERE edicionid='" . $edition -> id . "' AND userid
    		IN ( SELECT userid FROM " . $CFG -> prefix . "edicion_preinscripcion
    			 WHERE edicionid='" . $edition -> id . "' ) AND userid
    		NOT IN ( SELECT userid FROM " . $CFG -> prefix . "edicion_inscripcion
    				 WHERE edicionid='" . $edition -> id . "' )";
    if(!$count = $DB->get_record_sql($sql)) {
        return 0;
    }
    return $count -> count;
}

function mgm_get_user_inscription_by_edition($user, $edition) {
    global $CFG, $DB;

    $sql = "SELECT * FROM " . $CFG -> prefix . "edicion_inscripcion
    		WHERE edicionid = '" . $edition -> id . "' AND userid='" . $user -> id . "'
    		AND released!='0'";
    if(!$inscripcion = $DB->get_record_sql($sql)) {
        return false;
    }
    return $inscripcion;
}

/**
 * Get the certification scala
 *
 * @return mixed
 */
function mgm_get_certification_scala() {
    global $CFG, $DB;

    $sql = "SELECT * FROM " . $CFG -> prefix . "edicion_ite
    		WHERE type = " . MGM_ITE_SCALA . "";
    return $DB->get_record_sql($sql);
}

/**
 * Get the certification roles
 *
 * @return mixed
 */
function mgm_get_certification_roles() {
    global $CFG, $DB;

    $sql = "SELECT value FROM " . $CFG -> prefix . "edicion_ite
            WHERE type = " . MGM_ITE_ROLE . "";
    if($role = $DB->get_record_sql($sql)) {
        $roles = array('coordinador' => 0, 'tutor' => 0, 'estudiante' => 0);

        foreach(explode(",", $role->value) as $value) {
            $tmpvalue = explode(":", $value);
            $roles[$tmpvalue[0]] = $tmpvalue[1];
        }
        return $roles;
    } else {
        return false;
    }
}

/**
 * Sets the certification scala
 *
 * @param string $scala
 */
function mgm_set_certification_scala($scala) {
	global $DB;
    if(!$nscala = mgm_get_certification_scala()) {
        $nscala = new stdClass();
        $nscala -> type = MGM_ITE_SCALA;
        $nscala -> name = 'Scala';
        $nscala -> value = $scala;
        $DB->insert_record('edicion_ite', $nscala);
    } else {
        $nscala -> value = $scala;
        $DB->update_record('edicion_ite', $nscala);
    }
}

/**
 * Sets the certification roles
 *
 * @param string $roles
 */
function mgm_set_certification_roles($roles) {
	global $DB;
    $troles = "";
    $x = 1;
    foreach($roles as $k => $v) {
        if($x < count($roles)) {
            $troles .= $k . ":" . $v . ",";
        } else {
            $troles .= $k . ":" . $v;
        }
        $x++;
    }

    if(!$nroles = mgm_get_certification_roles()) {
        // New Record
        $nroles = new stdClass();
        $nroles -> type = MGM_ITE_ROLE;
        $nroles -> name = 'Roles';
        $nroles -> value = $troles;
        $DB->insert_record('edicion_ite', $nroles);
    } else {
        // Update Record
        $nroles = get_record('edicion_ite', 'type', MGM_ITE_ROLE);
        $nroles -> value = $troles;
        $DB->update_record('edicion_ite', $nroles);
    }
}

 /* Sets custom report ITE
 *
 * @param string $reportid
 * @param string $reporttype
 */
function mgm_set_report($reportid, $reporttype) {
	global $DB;
    if(!$nscala = mgm_get_certification_scala()) {
        $report = new stdClass();
        $report -> type = MGM_ITE_REPORT;
        $report -> name = $reporttype;
        $report -> value = $reportid;
        $DB->insert_record('edicion_ite', $report);
    } else {
        $report -> value = $reportid;
        $report -> name = $reporttype;
        $DB->update_record('edicion_ite', $report);
    }
}
function mgm_get_reports() {
    global $CFG, $DB;

    $sql = "SELECT * FROM " . $CFG -> prefix . "edicion_ite
    		WHERE type = " . MGM_ITE_REPORT . "";
    return $DB->get_records_sql($sql);
}

function mgm_get_courses($course) {
    global $CFG, $DB;

    $sql = "SELECT id, idnumber, fullname FROM " . $CFG -> prefix . "course
    		WHERE id !='" . $course -> id . "'
    		AND idnumber != '' GROUP BY idnumber";

    if(!$data = $DB->get_records_sql($sql)) {
        return array();
    }

    return $data;
}

function mgm_check_cert_history($userid, $courses, $dni=False){
	global $CFG, $DB;
	$ret=array(True, '', 0);
	$rcourses = array();
	foreach($courses as $course) {
  		if($course) {
    		$rcourses[] = $course;
    	}
  	}
	if(!count($rcourses)) {
    	return  $ret;
  	}
	$strcourses = implode(',', $rcourses);
	if ($euser = mgm_get_user_extend($userid)){
		$consulta='SELECT id, idnumber, fullname FROM  '. $CFG -> prefix .'course where  id in ('.$strcourses.')';
		$course_objs = $DB->get_records_sql($consulta);
		if ($dni){
			$euser->dni=$dni;
		}
		foreach($course_objs as $course){
			if (isset($course->idnumber) && isset($euser->dni) && $euser->dni != ''){
				if ($DB->record_exists('edicion_cert_history', 'numdocumento', $euser->dni, 'courseid', $course->idnumber, 'confirm', 1)){
					$ret[0]=false;
					$ret[1]=$ret[1]. get_string('coursecertfied', 'mgm', $course->fullname). '<br/>';
				 	$ret[2]=$course->id;
				}
			}
		}
	}
	return $ret;
}

/**
 * Returns true if the user has the required course dependencies
 *
 * @param object $edition
 * @param object $course
 * @param object $user
 * @return boolean
 */
function mgm_check_course_dependencies($edition, $course, $user, $dni=False) {
    global $CFG, $DB;

    if(!$criteria = mgm_get_edition_course_criteria($edition -> id, $course -> id)) {
        return true;
    }

    if(!isset($criteria -> depends) || !$criteria -> depends) {
        return true;
    }

    if(mgm_is_course_certified($user -> id, $criteria -> dlist)) {

        return true;
    }
		if ($euser = mgm_get_user_extend($user->id)){
			if ($course2 = $DB->get_record('course', id, $criteria->dlist)){
				if ($dni){
					$euser->dni=$dni;
				}
				if ($DB->record_exists('edicion_cert_history', 'numdocumento', $euser->dni, 'courseid', $course2->idnumber, 'confirm', 1) && $euser->dni != ''){
					return true;
				}
			}
		}
    return false;
}

/**
 * Returns the certifiation task for the given course
 *
 * @param string $course
 * @return object
 */
function mgm_get_certification_task($course) {
    global $CFG, $DB;

    $scala = mgm_get_certification_scala();

    $sql = "SELECT * FROM ".$CFG->prefix."grade_items
    		WHERE scaleid ='".$scala->value."'
    		AND courseid ='".$course."'";

    return $DB->get_record_sql($sql);
}

/**
 * Returns the grade for the given task and user
 *
 * @param object $task
 * @param object $user
 * @return object
 */
function mgm_get_grade($task, $user) {
	global $DB;
    $taskid = (is_object($task)) ? $task->id : $task;
    return $DB->get_record('grade_grades', 'itemid', $taskid, 'userid', $user->id);
}

/**
 * Return true if the user has passed the course otherwise returns false
 *
 * @param object $user
 * @param object $course
 * @return boolean
 */
function mgm_user_passed_course($user, $course) {
    if(!$ctask = mgm_get_certification_task($course->id)) {
        return false;
    }
    if(!$grade = mgm_get_grade($ctask, $user)) {
        return false;
    }

    return ($grade->finalgrade == $grade->rawgrademax);
}

/**
 * Helper class for courses interface
 */
function mgm_get_check_index($criteria) {
    $x = 0;
    foreach($criteria->dependencias as $k => $v) {
        if($criteria -> dlist == $k) {
            return $x;
        }
        $x++;
    }

    return $x;
}

/**
 * Set descartes in db for preinscription descartes
 *
 * @param string $editionid
 * @param string $courseid
 * @param string $states
 * @return bool
 */
function mgm_set_edicion_descartes($editionid, $courseid, $states){
	global $DB;
	$ret = true;
	$DB->delete_records('edicion_descartes', 'edicionid', $editionid, 'courseid', $courseid);
	foreach($states as $userid=>$code){
		$reg=new stdClass();
		$reg->edicionid=$editionid;
		$reg->courseid=$courseid;
		$reg->userid=$userid;
		$reg->code=$code;
		$d = $DB->insert_record('edicion_descartes', $reg);
		if (!$d){
			$ret = false;
		}
	}
	return $ret;
}

/**
 * Get descartes array
 *
 * @param string $editionid
 * @param string $userid
 * @return array which key=course and value=code
 */
function mgm_get_edicion_descartes($editionid, $userid){
	global $CFG, $DB;
	$sql="select * from ".$CFG->prefix."edicion_descartes where edicionid=".$editionid." and userid=" .$userid;
	if ($regs = $DB->get_records_sql($sql)){
		$dev=array();
		foreach ($regs as $reg){
			$dev[$reg->courseid]=$reg->code;
		}
	  return $dev;
	}
	return false;
}


/**
 * Return the user certification history
 *
 * @param string $userid
 * @return object
 */
function mgm_get_cert_history($userid) {
	global $DB;
    if(!$userid) {
        return false;
    }
    return get_records('edicion_cert_history', 'userid', $userid);
}

function mgm_get_cert_history_str($userid) {
	global $DB;
	$str='';
   	foreach(mgm_get_cert_history($userid) as $cert){
   		if ($course = $DB->get_record('course', 'id', $cert->courseid)){
   			$str=$str.$course->fullname.'(reg:'.$cert->numregistro .', confirm: '.$cert->confirm.")\n";
   		}
   	}
   	return $str;
}

/**
 * Return true if user given by userid has certified the course given by courseid
 *
 * @param string $userid
 * @param string $courseid
 * @return boolean
 */
function mgm_is_course_certified($userid, $courseid) {
	global $DB;
    if(!$userid || !$courseid) {
        return false;
    }

    if(!$cert = $DB->get_record('edicion_cert_history', 'userid', $userid, 'courseid', $courseid)) {
        return false;
    } else {
        return true;
    }
}

/**
 * Certificates a course
 *
 * @param string $userid
 * @param string $courseid
 * @param object $edition
 * @param string $roleid
 * @return boolean
 */
function mgm_certificate_course($userid, $courseid, $edition, $roleid = 0, $tipodocumento=false, $numdocumento=false) {
    global $CFG, $DB;

    if(!$userid || !$courseid || !$edition || !$roleid) {
        return false;
    }

    if(!mgm_edition_is_on_validate($edition)) {
        return false;
    }

    $sql = "SELECT * FROM " . $CFG -> prefix . "edicion_cert_history
            WHERE userid=" . $userid . "
            AND courseid=" . $courseid . "
            AND edicionid=" . $edition -> id;

    if(!$data = $DB->get_record_sql($sql)) {
        $data = new stdClass();
        $data -> userid = $userid;
        $data -> courseid = $courseid;
        $data -> edicionid = $edition -> id;
        $data -> roleid = $roleid;
        if ($tipodocumento){
        	$data -> tipodocumento = $tipodocumento;
        }
        if ($numdocumento){
        	$data -> numdocumento = $numdocumento;
        }

        $data -> confirm = true;

        return $DB->insert_record('edicion_cert_history', $data);
    }

    $data -> confirm = true;
    return $DB->update_record('edicion_cert_history', $data);
}

/**
 * Certficates an edition
 *
 * @param object $edition
 * @return boolean
 */
function mgm_certificate_edition($edition) {
	global $DB;
    if(!$edition) {
        return false;
    }

    foreach(mgm_get_edition_courses($edition) as $course) {
        if(!$participants = mgm_get_course_participants($course)) {
            return false;
        }

        $participants = mgm_check_double_role_in_course($participants);

        $course_participants = array();
        foreach($participants as $participant) {
            if(!mgm_user_passed_course($DB->get_record('user', 'id', $participant -> userid), $course)) {
                continue ;
            }

            if(!mgm_certificate_course($participant -> userid, $course -> idnumber, $edition, $participant -> roleid, $participant -> tipoid, $participant -> dni)) {
                return false;
            }
        }
    }

    return true;
}

/**
 * Check if there exists dupe ids in a participants array.
 * If exists, just certificate the highest roleid
 *
 * @param array $participants
 * @return array
 */
function mgm_check_double_role_in_course($participants) {
    $ardy = array();
    if(!$participants) {
        return $ardy;
    }
    foreach($participants as $k => $v) {
        if(array_key_exists($v -> userid, $ardy)) {
            // Double found
            $ardy[$v -> userid] = mgm_get_highest_role($ardy[$v -> userid], $v);
        } else {
            $ardy[$v -> userid] = $v;
        }
    }

    return $ardy;
}

/**
 * Return the highest role
 *
 * @param object $rol1
 * @param object $rol2
 * @return object
 */
function mgm_get_highest_role($rol1, $rol2) {
    $roles = mgm_get_certification_roles();
    $role1_level = $role2_level = 0;

    switch ($rol1->roleid) {
        case $roles['coordinador'] :
            $role1_level = 3;
            break;
        case $roles['tutor'] :
            $role1_level = 2;
            break;
        case $roles['alumno'] :
            $role1_level = 1;
            break;
    }

    switch ($rol2->roleid) {
        case $roles['coordinador'] :
            $role2_level = 3;
            break;
        case $roles['tutor'] :
            $role2_level = 2;
            break;
        case $roles['alumno'] :
            $role2_level = 1;
            break;
    }

    if($role1 > $role2) {
        return $role1;
    } else {
        return $role2;
    }
}

function mgm_get_pass_courses($editionid, $userid) {
    if(!$editionid || !$userid) {
        return false;
    }

    if(!$ctask = mgm_get_certification_task($course)) {

    }
}

function mgm_get_course_participants($course, $real = false) {
    global $CFG, $DB;

    if(!$course) {
        return false;
    }

    if(!$context = get_context_instance(CONTEXT_COURSE, $course -> id)) {
        error('No context found');
    }

    $sitecontext = get_context_instance(CONTEXT_SYSTEM);
    $frontpagectx = get_context_instance(CONTEXT_COURSE, SITEID);

    $adminroles = array();
    if($roles = get_roles_used_in_context($context, true)) {
        $canviewroles = get_roles_with_capability('moodle/course:view', CAP_ALLOW, $context);
        $doanythingroles = get_roles_with_capability('moodle/site:doanything', CAP_ALLOW, $sitecontext);

        if($context -> id == $frontpagectx -> id) {
            //we want admins listed on frontpage too
            foreach($doanythingroles as $dar) {
                $canviewroles[$dar -> id] = $dar;
            }
            $doanythingroles = array();
        }

        foreach($roles as $role) {
            if(!isset($canviewroles[$role -> id])) {   // Avoid this role (eg course creator)
                $adminroles[] = $role -> id;
                unset($roles[$role -> id]);
                continue ;
            }
            if(isset($doanythingroles[$role -> id])) {   // Avoid this role (ie admin)
                $adminroles[] = $role -> id;
                unset($roles[$role -> id]);
                continue ;
            }
        }
    }

    if($real)
        $select = "SELECT DISTINCT u.id as userid, ctx.id as ctxid, u.username, r.roleid, eu.tipoid, eu.dni ";
    else
        $select = "SELECT DISTINCT ctx.id, u.id as userid, u.username, r.roleid, eu.tipoid, eu.dni ";
    $from = "FROM ".$CFG->prefix."user u " .
            "LEFT OUTER JOIN " .$CFG->prefix."context ctx " .
            "ON (u.id=ctx.instanceid AND ctx.contextlevel=".CONTEXT_USER.") ".
            "JOIN ".$CFG->prefix."role_assignments r ".
            "ON u.id=r.userid ".
    		"LEFT JOIN ". $CFG->prefix ."edicion_user eu ON u.id=eu.userid ";

    // we are looking for all users with this role assigned in this context or higher
    if($usercontexts = get_parent_contexts($context)) {
        $listofcontexts = '(' . implode(',', $usercontexts) . ')';
    } else {
        $listofcontexts = '(' . $sitecontext -> id . ')';
        // must be site
    }

    if($real)
        $where = "WHERE (r.contextid = " . $context -> id . " OR r.contextid in " . $listofcontexts . ") " . "AND u.deleted = 0 " . "AND u.username != 'guest' " . "AND r.roleid NOT IN (" . implode(',', $adminroles) . ")";
    else
        $where = "WHERE (r.contextid = " . $context -> id . ") " . "AND u.deleted = 0 " . "AND u.username != 'guest' " . "AND r.roleid NOT IN (" . implode(',', $adminroles) . ")";

    return $DB->get_records_sql($select . $from . $where);
}

/**
 * Funcion para validar un CIF NIF o NIE
 *
 * @param string $nif
 * @return string
 */
function mgm_validate_cif($cif) {
    $cif = strtoupper($cif);
    for($i = 0; $i < 9; $i++) {
        $num[$i] = substr($cif, $i, 1);
    }

    // Si no tiene un formato valido devuelve error
    if(!ereg('((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)', $cif)) {
        return false;
    }

    // Comprobacion de NIFs estandar
    if(ereg('(^[0-9]{8}[A-Z]{1}$)', $cif)) {
        if($num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr($cif, 0, 8) % 23, 1)) {
            return true;
        } else {
            return false;
        }
    }

    // Algoritmo para comprobacion de codigos tipo CIF
    $suma = $num[2] + $num[4] + $num[6];
    for($i = 1; $i < 8; $i += 2) {
        $suma += substr((2 * $num[$i]), 0, 1) + substr((2 * $num[$i]), 1, 1);
    }

    $n = 10 - substr($suma, strlen($suma) - 1, 1);
    // Comprobacion de NIFs especiales (se calculan como CIFs)
    if(ereg('^[KLM]{1}', $cif)) {
        if($num[8] == chr(64 + $n)) {
            return true;
        } else {
            return false;
        }
    }

    // Comprobacion de CIFs
    if(ereg('^[ABCDEFGHJNPQRSUVW]{1}', $cif)) {
        if($num[8] == chr(64 + $n) || $num[8] == substr($n, strlen($n) - 1, 1)) {
            return true;
        } else {
            return false;
        }
    }

    //comprobacion de NIEs
    //T
    if(ereg('^[T]{1}', $cif)) {
        if($num[8] == ereg('^[T]{1}[A-Z0-9]{8}$', $cif)) {
            return true;
        } else {
            return false;
        }
    }

    //XYZ
    if(ereg('^[XYZ]{1}', $cif)) {
        if($num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr(str_replace(array('X', 'Y', 'Z'), array('0', '1', '2'), $cif), 0, 8) % 23, 1)) {
            return true;
        } else {
            return false;
        }
    }

    // Si todavia no se ha verificado devuelve error
    return false;
}

/**
 * Download a sheet document with the given data
 *
 * @param $fields
 */
function mgm_download_doc($fields) {
    switch($fields['filetype']) {
        case 'xls' :
            $workbook = new MoodleExcelWorkbook('-');
            $filename = clean_filename($fields['filename'] . '.xls');
            break;
        case 'ods' :
            $workbook = new MoodleODSWorkbook('-');
            $filename = clean_filename($fields['filename'] . '.ods');
            break;
        default :
            error('Unknown file type in mgm_download_doc ' . $fields['filetype']);
            break;
    }
    $workbook->send($filename);

    $worksheet = array();
    $worksheet[0] = &$workbook->add_worksheet('');

    $col = 0;
    foreach($fields['header'] as $fieldname) {
        $worksheet[0]->write(0, $col, $fieldname);
        $col++;
    }

    $row = 1;
    foreach($fields['data'] as $data) {
        $col = 0;
        foreach($data as $fdata) {
            $worksheet[0]->write($row, $col, $fdata);
            $col++;
        }
        $row++;
    }

    $workbook->close();
    die();
}

/**
 * Create some needed data on database if not exists
 */
function mgm_create_especs() {
    global $CFG, $DB;
    global $MGM_ITE_ESPECS;

    if(!mgm_especs_exists()) {
        // No data exists. Just create it
        $nespecs = new stdClass();
        $nespecs -> type = MGM_ITE_ESPECIALIDADES;
        $nespecs -> name = 'Especialidades';
        $nespecs -> value = implode("\n", $MGM_ITE_ESPECS);
        $DB->insert_record('edicion_ite', $nespecs);
    }

    return true;
}

function mgm_update_especs() {
    global $CFG, $DB;
    global $MGM_ITE_ESPECS;
		$sql = "SELECT id FROM {edicion_ite}
            WHERE type = ?";
    if($especs = $DB->get_record_sql($sql, array(MGM_ITE_ESPECIALIDADES))) {
        //Data exist. Just update it
        $especs -> type = MGM_ITE_ESPECIALIDADES;
        $especs -> name = 'Especialidades';
        $especs -> value = implode("\n", $MGM_ITE_ESPECS);
        $dev = $DB->update_record('edicion_ite', $especs);
    }
		if ($dev){
			return true;
		}else{
			return false;
		}
    return false;
}

/**
 * Check if especialidades data exists on the database. If exists returns it
 * if doesn't exists just returns false
 *
 * @return mixed
 */
function mgm_especs_exists() {
    global $CFG, $DB;

    $sql = "SELECT value FROM " . $CFG -> prefix . "edicion_ite
            WHERE type = " . MGM_ITE_ESPECIALIDADES . "";

    return $DB->get_record_sql($sql);
}

function mgm_get_microtime() {
    $temp = explode(" ", microtime());
    return bcadd($temp[0], $temp[1], 6);
}

function mgm_get_elapsed($start, $end) {
    return bcsub($start, $end, 6);
}

function mgm_get_usuarios_no_inscritos($edicion, $search='', $page=0, $recordsperpage=30) {
    global $CFG, $DB;

    $LIKE = sql_ilike();
    $fullname = sql_concat("a.firstname", "' '", "a.lastname");

    $sql = "SELECT p.userid, p.timemodified, p.value, a.firstname, a.lastname,
                a.email, u.dni, u.cc, u.especialidades
            FROM " . $CFG -> prefix . "edicion_preinscripcion AS p LEFT JOIN " . $CFG -> prefix . "user AS a
            ON ( a.id = p.userid ) LEFT JOIN " . $CFG -> prefix . "edicion_user AS u
            ON ( u.userid = a.id ) WHERE edicionid='" . $edicion . "'
            AND p.userid NOT IN
            ( SELECT userid FROM " . $CFG -> prefix . "edicion_inscripcion
              WHERE edicionid='" . $edicion . "' )";

    if(!empty($search)) {
        $search = trim($search);
        $sql .= " AND ( " . $fullname . " " . $LIKE . " '%" . $search . "%' OR a.email " . $LIKE . " '%" . $search . "%')";
    }

    $users = $DB->get_records_sql($sql, $page * $recordsperpage, $recordsperpage);

    $sql = "SELECT COUNT(p.id) as userscount FROM " . $CFG -> prefix . "edicion_preinscripcion
            AS p LEFT JOIN " . $CFG -> prefix . "user AS a ON ( a.id = p.userid )
            WHERE edicionid='" . $edicion . "' AND p.userid NOT IN
            ( SELECT userid FROM " . $CFG -> prefix . "edicion_inscripcion
              WHERE edicionid='" . $edicion . "' )";

    if(!empty($search)) {
        $search = trim($search);
        $sql .= " AND ( " . $fullname . " " . $LIKE . " '%" . $search . "%' OR a.email " . $LIKE . " '%" . $search . "%' )";
    }

    $users_count = $DB->get_record_sql($sql);

    return array('users' => $users, 'userscount' => $users_count -> userscount);
}

function mgm_courses_from_user_choices($choices) {
    global $CFG, $DB;

    $sql2 = "SELECT id, fullname FROM " . $CFG -> prefix . "course
             WHERE id IN (" . $choices . ")";

    return $DB->get_records_sql($sql2);
}

function mgm_get_edition_payment_data($edition, &$data) {
    $data = array();
    foreach(mgm_get_edition_courses($edition) as $course) {
        $groups = groups_get_all_groups($course->id);
        foreach($groups as $k=>$v) {
            if ($groupmemberroles = groups_get_members_by_role($k,$course->id)) {

            }
        }
        $data[] = array(
            'course' => array(
                'id' => $course->id,
                'name' => $course->shortname,
                'fullname' => $course->fullname
            ),
            'criteria' => mgm_get_edition_course_criteria($edition->id, $course->id),
            'ecuador' => mgm_get_course_ecuador($course->id),
            'alumnos' => mgm_get_course_tutor_payment_count($course),
            'coordinacion' => mgm_get_course_coordinador_payment($course),
            'grupos' => mgm_get_course_tutor_payment($course)
        );
    }

    return true;
}

function mgm_get_course_tutor_payment($course) {
    $data = array();
    $rolesid = mgm_get_certification_roles();
    $edition = mgm_get_course_edition($course->id);
    $criteria = mgm_get_edition_course_criteria($edition->id, $course->id);
    $firsttask = mgm_get_course_first_task($course->id);
    $ctask = mgm_get_certification_task($course->id);

    if (!$ecuador = mgm_get_course_ecuador($course->id)) {
        return $data;
    }


    if ($groups = groups_get_all_groups($course->id)) {
        foreach($groups as $k=>$v) {
            $tmp_data = array(
                'group' => $v,
                'result' => array(
                    'full' => array('amount' => 0, 'count' => 0),
                    'half' => array('amount' => 0, 'count' => 0),
                    'dont_start' => array('amount' => 0, 'count' => 0)
                ),
                'tutor' => array(),
                'alumnos' => array()
            );

            if ($groupmemberroles = groups_get_members_by_role($k, $course->id)) {
                foreach($groupmemberroles as $key=>$roledata) {
                    if ($key == '*') {
                        // Multiple roles
                        foreach($roledata->users as $user) {
                            foreach($user->roles as $role) {
                                if($role->id == $rolesid['tutor']) {
                                    $tmp_data['tutor'][] = array(
                                        'id' => $user->id,
                                        'firstname' => $user->firstname,
                                        'lastname' => $user->lastname,
                                        'email' => $user->email
                                    );
                                }
                            }
                        }
                    } else if($key == $rolesid['tutor']) {
                        // Tutor role
                        foreach($roledata->users as $user) {
                            $tmp_data['tutor'][] = array(
                                'id' => $user->id,
                                'firstname' => $user->firstname,
                                'lastname' => $user->lastname,
                                'email' => $user->email
                            );
                        }

                    } else if($key == $rolesid['alumno']) {
                        // Alumno role
						$result = mgm_calculate_tutor_payment($course, $criteria, $firsttask, $roledata->users);
                        $tmp_data['result'] = $result['data'];
						$tmp_data['alumnos'] = $result['alumnos'];
                    }
                }
            }

            $data[] = $tmp_data;
        }
    }

    return $data;
}

function mgm_get_course_coordinador_payment($course) {
	global $DB;
    if (!$tutors = mgm_get_course_tutors($course)) {
        return array();
    }

    $edition = mgm_get_course_edition($course->id);
    $criteria = mgm_get_edition_course_criteria($edition->id, $course->id);

    $amount_per_tutor = mgm_get_tramo_amount($criteria, count($tutors));

    $coord = mgm_get_course_coordinators($course);
    return array(
        'user' => $coord,
        'edicion_user' => $DB->get_record('edicion_user', 'userid', $coord->id),
        'total_amount' => (($criteria->duration+1) * $amount_per_tutor) + $criteria->prevlab,
        'tutors' => $tutors,
        'amount_per_tutor' => $amount_per_tutor,
        'duration' => $criteria->duration,
        'prevlab' => $criteria->prevlab
    );
}

function mgm_get_tramo_amount($criteria, $num_tutors) {
    if ($num_tutors < 6) {
        return $criteria->tramo[0];
    } else if ($num_tutors >= 6 && $num_tutors < 11) {
        return $criteria->tramo[1];
    } else if ($num_tutors >= 11 && $num_tutors < 16) {
        return $criteria->tramo[2];
    } else if ($num_tutors >= 16 && $num_tutors < 21) {
        return $criteria->tramo[3];
    } else {
        return $criteria->tramo[4];
    }
}

function mgm_get_course_tasks($courseid) {
    global $CFG, $DB;

    $sql = "SELECT * FROM ".$CFG->prefix."grade_items
            WHERE courseid=".$courseid."
            AND itemtype !='course' ORDER by sortorder";

    return $DB->get_records_sql($sql);
}

function mgm_get_course_ecuador($courseid) {
	global $CFG;
	$dev=null;
    $edition = mgm_get_course_edition($courseid);
    $criteria = mgm_get_edition_course_criteria($edition->id, $courseid);
    if ($criteria->ecuadortask != MGM_ECUADOR_DEFAULT) {
        $dev=$criteria->ecuadortask;
    }else{
    	$sql = "SELECT * FROM ".$CFG->prefix."grade_items
            WHERE courseid=".$courseid."
            AND itemtype !='course' and itemname like 'ec-%' ORDER by sortorder";
    	$dev= array_pop($DB->get_records_sql($sql));
		if (! $dev){
	    	$tasks = mgm_get_course_tasks($courseid);
	    	$x = 1;
	    	foreach ($tasks as $task) {
	    		if ($x == round(count($tasks) / 2)) {
	    			return $task;
	    		}
	    		$x++;
	    	}
	    }
    }
    return $dev;
}

function mgm_get_course_first_task($courseid) {
    global $CFG, $DB;
		$dev=false;
    $sql = "SELECT * FROM ".$CFG->prefix."grade_items
            WHERE courseid=".$courseid."
            AND itemtype !='course' and itemname like 'presentac%' ORDER BY sortorder LIMIT 1";
    if ($reg = $DB->get_records_sql($sql)){
    	$dev = array_pop($reg);
    }else{
    	$sql = "SELECT * FROM ".$CFG->prefix."grade_items
            WHERE courseid=".$courseid."
            AND itemtype !='course' ORDER BY sortorder LIMIT 1";
			if ($reg = $DB->get_records_sql($sql))
    		$dev = array_pop($reg);
    }
    return $dev;
}

function mgm_get_course_tutor_payment_count($course) {
    if (!$students = mgm_get_course_students($course)) {
        return false;
    }

    $edition = mgm_get_course_edition($course->id);
    $criteria = mgm_get_edition_course_criteria($edition->id, $course->id);
    $firsttask = mgm_get_course_first_task($course->id);

    return mgm_calculate_tutor_payment($course, $criteria, $firsttask, $students);
}

function mgm_calculate_tutor_payment($course, $criteria, $firsttask, $students) {
  //$ctask = mgm_get_certification_task($course->id);

	$result = array(
		'data' => array(
	        'dont_start' => array('count' => 0, 'amount' => 0),
	        'half' => array('count' => 0, 'amount' => 0),
	        'full' => array('count' => 0, 'amount' => 0)
	    ),
		'alumnos' => array()
	);

    if ($criteria->ecuadortask){
      $ecuador=$criteria->ecuadortask;
    }else{
    	$ecuador=mgm_get_course_ecuador($course->id);
    }

    foreach ($students as $student) {
		$result['alumnos'][] = array(
			'id' => $student->id,
		    'firstname' => $student->firstname,
		    'lastname' => $student->lastname
		);

        if (!$fgrade = mgm_get_grade($firsttask, $student)) {
            $result['data']['dont_start']['count']++;
            continue;
        }

        if ($fgrade->finalgrade != $firsttask->grademax) {
            $result['data']['dont_start']['count']++;
            continue;
        }
      if ($mgrade = mgm_get_grade($ecuador, $student)) {
				if($mgrade->finalgrade == $mgrade->rawgrademax) {
						$result['data']['full']['count']++;
	        	$result['data']['full']['amount'] += $criteria->tutorpayment;
				}else{
						$result['data']['half']['count']++;
	          $result['data']['half']['amount'] += ($criteria->tutorpayment * 0.50);
				}
       } else {
            $result['data']['half']['count']++;
            $result['data']['half']['amount'] += ($criteria->tutorpayment * 0.50);
       }
    }

    return $result;
}

function mgm_get_course_students($course) {
    return mgm_get_course_roles_on_demand($course, 'alumno');
}

function mgm_get_course_tutors($course) {
    return mgm_get_course_roles_on_demand($course, 'tutor');
}

function mgm_get_course_coordinators($course) {
    return mgm_get_course_roles_on_demand($course, 'coordinador');
}

function mgm_get_course_roles_on_demand($course, $role) {
    global $CFG, $DB;

    if(!$course) {
        return false;
    }

    if(!$context = get_context_instance(CONTEXT_COURSE, $course->id)) {
        error('No context found');
    }

    $roles = mgm_get_certification_roles();
    $sql = "SELECT DISTINCT u.id, u.username, u.firstname, u.lastname, u.email, ctx.id AS ctxid, ctx.contextlevel AS ctxlevel
            FROM ".$CFG->prefix."user u
            LEFT OUTER JOIN ".$CFG->prefix."context ctx ON (u.id=ctx.instanceid AND ctx.contextlevel=".CONTEXT_USER.")
            JOIN ".$CFG->prefix."role_assignments r ON u.id=r.userid
            LEFT OUTER JOIN ".$CFG->prefix."user_lastaccess ul ON (r.userid=ul.userid and ul.courseid=".$course->id.")
            WHERE (r.contextid=".$context->id.")
            AND u.deleted = 0  AND r.roleid=".$roles[$role]." AND (ul.courseid=".$course->id." OR ul.courseid IS NULL)
            AND u.username != 'guest'";

    return ($role != 'coordinador') ? $DB->get_records_sql($sql) : $DB->get_record_sql($sql);
}

function mgm_get_user_dni($userid) {
	global $DB;
    if(!$user = $DB->get_record('edicion_user', 'userid', $userid)) {
        return 'NOT SET';
    }

    return ($user->dni != '') ? $user->dni : 'NOT SET';
}
function mgm_get_userid_from_dni($numdocumento) {
	global $DB;
	if ($field = $DB->get_record('user_info_field','shortname', 'nifniepasaporte')){
		$sql='SELECT u.id, u.idnumber, eu.dni, uid.data  FROM mdl_edicion_user eu, mdl_user u, mdl_user_info_data uid where eu.userid=u.id  and uid.userid=u.id and uid.fieldid='.$field->id;
		$sql=$sql . ' and ( UPPER(eu.dni) = '.strtoupper($numdocumento);
		$sql=$sql . ' or UPPER(uid.data) = '.strtoupper($numdocumento);
		$sql=$sql . ' or UPPER(u.idnumber) = '.strtoupper($numdocumento) . ' )';
	}else{
		$sql='SELECT u.id, u.idnumber, eu.dni FROM mdl_edicion_user eu, mdl_user u where eu.userid=u.id';
		$sql=$sql . ' and ( UPPER(eu.dni) = '.strtoupper($numdocumento);
		$sql=$sql . ' or UPPER(u.idnumber) = '.strtoupper($numdocumento) . ' )';
	}
	if ($obj = $DB->get_record_sql($sql)){
		return (int)$obj->id;
	}else{
		return false;
	}
}

function mgm_set_cert_history($reg){
	global $DB;
	$hr = $DB->get_record('edicion_cert_history', 'numdocumento', $reg->numdocumento, 'courseid', $reg->courseid);
	if ($hr && $hr->confirm==1){
		return array(false, 'Documento: '.$reg->numdocumento.', Curso '. $reg->courseid.' NO ACTION <br/>');
	}else if ($hr && $hr->confirm==0){
		$reg->id=$hr->id;
		$dev = $DB->update_record('edicion_cert_history',$reg);
		return array(true, 'Documento: '.$reg->numdocumento.', Curso '. $reg->courseid.' U <br/>');
	}else{
		$dev = $DB->insert_record('edicion_cert_history',$reg);
		return array(true, 'Documento: '.$reg->numdocumento.', Curso '. $reg->courseid.' C <br/>');;
	}
}

function mgm_set_centro($reg){
	global $DB;
	if ($oldreg = $DB->get_record('edicion_centro', 'codigo', $reg->codigo)){
		$reg->id=$oldreg->id;
		$DB->update_record('edicion_centro',$reg);
		return array(false, 'Centro: '.$reg->codigo.' UPDATE<br/>');
	}else {
		$dev = $DB->insert_record('edicion_centro',$reg);
		return array(true, 'Centro: '.$reg->codigo.' ADD<br/>');
	}
}


function mgm_parse_msg($msg, $userid, $editionid){
	 global $CFG, $DB;
     $arol='5';
     $sql="
     SELECT u.username usuario, u.firstname nombre,u.lastname apellidos, eu.dni dni, eu.cc, u.email, c.fullname as curso, ect.dgenerica, ect.despecifica, ect.direccion, ect.cp , ect.localidad, ect.provincia, ect.pais, ect.telefono
     FROM mdl_user u left join  mdl_edicion_user eu on (u.id=eu.userid) left join mdl_edicion_centro ect on ect.codigo=eu.cc,
     mdl_edicion_course ec left join mdl_course c on ec.courseid=c.id , mdl_role_assignments AS ra INNER JOIN mdl_context AS context ON ra.contextid=context.id
     where context.contextlevel = 50 AND ra.roleid=".$arol." AND u.id=ra.userid AND context.instanceid=ec.courseid AND u.id=".$userid." AND ec.edicionid=". $editionid;
     $sql=str_replace("prefix_", $CFG->prefix, $sql);
     if($reg = $DB->get_record_sql($sql)){
       $fields=array('usuario', 'nombre', 'apellidos', 'dni', 'cc', 'email', 'curso', 'dgenerica', 'despecifica', 'direccion', 'cp', 'localidad', 'provincia', 'pais', 'telefono');
       foreach($fields as $field){
     	   $msg=str_replace('#'.$field, $reg->$field, $msg);
       }
     }
     return $msg;
}

function mgm_parse_letter($data, $userid, $editionid){
	   global $CFG, $DB;
     $arol='5';
     $sql="
     SELECT u.username usuario, u.firstname nombre,u.lastname apellidos, eu.dni dni, eu.cc, u.email, c.fullname as curso, ect.dgenerica, ect.despecifica, ect.direccion, ect.cp , ect.localidad, ect.provincia, ect.pais, ect.telefono
     FROM mdl_user u left join  mdl_edicion_user eu on (u.id=eu.userid) left join mdl_edicion_centro ect on ect.codigo=eu.cc,
     mdl_edicion_course ec left join mdl_course c on ec.courseid=c.id , mdl_role_assignments AS ra INNER JOIN mdl_context AS context ON ra.contextid=context.id
     where context.contextlevel = 50 AND ra.roleid=".$arol." AND u.id=ra.userid AND context.instanceid=ec.courseid AND u.id=".$userid." AND ec.edicionid=". $editionid;
     $sql=str_replace("prefix_", $CFG->prefix, $sql);
     if($reg = $DB->get_record_sql($sql)){
     	$letter=new stdClass();
     	$fields=array('usuario', 'nombre', 'apellidos', 'dni', 'cc', 'email', 'curso', 'dgenerica', 'despecifica', 'direccion', 'cp', 'localidad', 'provincia', 'pais', 'telefono');
     	foreach($fields as $field){
     		$data->letterhead=str_replace('#'.$field, $reg->$field, $data->letterhead);
     		$data->letterbody=str_replace('#'.$field, $reg->$field, $data->letterbody);
     		$data->letterfoot=str_replace('#'.$field, $reg->$field, $data->letterfoot);
       }
       return $data;
     }
     return false;
}

function mgm_get_string($str){
	$dev=get_string($str);
	if ($dev[0] == '['){
		$dev=str_replace('[','',$dev);
		$dev=str_replace(']','',$dev);
		$dev=ucfirst($dev);
	}
	return $dev;
}

/*
 * Class
 */
class Edicion {
    var $data;
    var $anoacademico = null;

    function Edicion($data = null) {
        if(!$data)
            $this -> data = mgm_get_active_edition();
        else
            $this -> data = $data;
    }

    function getFin() {
        return $this -> data -> fin;
    }

    function getAnoAcademico() {
        if(!$this -> anoacademico) {
            $yday = date("z", $this -> data -> inicio);
            $year = date("Y", $this -> data -> inicio);
            //Inicio de año academico el 15 de septiembre
            //TODO: Parametrizar?
            if($yday < date("z", mktime(0, 0, 0, 7, 1, 2011))) {
                $year--;
            }
            $this -> anoacademico = $year . $year + 1;
        }
        return $this -> anoacademico;
    }

    function getCursos($show=false) {
        $cursosdata = mgm_get_edition_courses($this -> data);
        $cursos = array();
        $numactividad = 1;
        if($cursosdata)
            foreach($cursosdata as $cursodata) {
                $cursos[$cursodata -> id] = new Curso($cursodata, $this, $numactividad, $show);
                $numactividad++;
            }
        return $cursos;
    }

}

class Curso {
    var $data;
    var $participantes = null;
    var $dparticipantes = False;
    var $edata = array();
    var $dbedata;
    var $edicion;
    var $incidencias = array();
    var $info;
    var $tareas = array();
    var $show=array('norol'=>1, 'nodni'=>1, 'usuario'=>1, 'curso'=>1);
    function format_text($str){
    	  if ($str){

    	   return '"'.str_replace('"','',$str).'"';
    	  }
    	  return $str;
    }

    function cargarEdata($campo, $ncampo) {
        if(!$campo) {
            $this -> info -> campo = $ncampo;
            if($this->show['curso']){
            	$this -> incidencias[] = get_string('incidencia_curso', 'mgm', $this -> info);
            }
        }
        $this -> edata[$ncampo] = $campo;
    }

    function Curso($data, $edicion, $numactividad, $show=false) {
        $this -> data = $data;
        $this -> edicion = $edicion;
        $this -> dbedata = mgm_get_edition_course($edicion -> data -> id, $data -> id);

        $this -> info -> curso = $this -> data -> fullname;
        $this -> info -> edicion = $this -> edicion -> data -> name;
        $this -> info -> cursoid = $this -> data -> id;
        $this -> info -> edicionid = $this -> edicion -> data -> id;
    		if($show){
    	  	foreach($show as $k=>$s){
    	  		$this->show[$k]=$s;
    	  	}
    	  }


        $this -> edata['anoacademico'] = $this->format_text($this -> edicion -> getAnoAcademico());
        #Obligatorio

        #TODO: Parametrizar?
        $this -> edata['codentidad'] = $this->format_text('28923065');
        #Obligatorio

        #TODO: Parametrizar?
        $this -> edata['codentidadvisado'] = $this->format_text('28923016');
        #Obligatorio

        $this -> edata['codtipoactividad'] = $this->format_text('AP');
        #Obligatorio

        #Nos tiene que venir de vuelta
        $this -> edata['numactividad'] = $numactividad;


        $this -> cargarEdata($this -> dbedata -> codagrupacion, 'codagrupacion');
        #Obligatorio

        #Nos tiene que venir de vuelta
        $this -> edata['codactividad'] = $this->format_text($this -> edata['codentidad'] . sprintf('%04d', $numactividad));
        #$this -> edata['codactividad'] = null;
        #Obligatorio

        $this -> cargarEdata($this->format_text($this -> dbedata -> codmodalidad), 'codmodalidad');
        #Obligatorio

        #TODO: Parametrizar?
        $this -> edata['codentidadpadre'] = $this->format_text('28923016');
        #Obligatorio

        $this -> cargarEdata($this->format_text($this -> dbedata -> codprovincia), 'codprovincia');
        #Obligatorio

        $this -> cargarEdata($this->format_text($this -> dbedata -> codpais), 'codpais');
        #Obligatorio

        $this -> cargarEdata($this->format_text($this -> dbedata -> codmateria), 'codmateria');
        #Obligatorio

        $this -> cargarEdata($this->format_text($this -> dbedata -> codniveleducativo), 'codniveleducativo');
        #Obligatorio

        #No es necesario rellenarlo, sería necesario rellenar mediante interfaz
        $this -> edata['codambito'] = null;

        #No es necesario rellenarlo, aunque es calculable
        $this -> edata['numsolicitudes'] = null;

        #No es necesario rellenarlo, aunque es calculable
        $this -> edata['numasistentes'] = null;

        #No es necesario rellenarlo, aunque es calculable
        $this -> edata['numfinalizados'] = null;

        $this -> cargarEdata($this -> dbedata -> numhoras, 'numhoras');
        #Obligatorio

        $this -> cargarEdata($this -> dbedata -> numcreditos, 'numcreditos');
        #Obligatorio

        #No es necesario rellenarlo
        $this -> edata['fechainicioprevista'] = null;

        #No es necesario rellenarlo
        $this -> edata['fechafinprevista'] = null;

        $this -> cargarEdata(date('d/m/y H:i:s', $this -> dbedata -> fechainicio), 'fechainicio');
        #Obligatorio

        $this -> cargarEdata(date('d/m/y H:i:s', $this -> dbedata -> fechafin), 'fechafin');
        #Obligatorio

        #No es necesario rellenarlo
        $this -> edata['generaacta'] = null;

        #No es necesario rellenarlo
        $this -> edata['actagenerada'] = null;

        #No es necesario rellenarlo
        $this -> edata['fechagenacta'] = null;

        #No es necesario rellenarlo
        $this -> edata['generacertif'] = null;

        #No es necesario rellenarlo
        $this -> edata['tema'] = null;

        $this -> edata['titulo'] = $this->format_text(mb_strtoupper($this -> data -> fullname, 'utf-8'));
        #Obligatorio

        #No es necesario rellenarlo
        $this -> edata['idsexenios'] = null;

        #No es necesario rellenarlo
        $this -> edata['numregCCAA'] = null;

        #No es necesario rellenarlo
        $this -> edata['textocertif'] = null;

        #No es necesario rellenarlo
        $this -> edata['centroeducativo'] = null;

        $this -> cargarEdata($this->format_text(mb_strtoupper($this -> dbedata -> localidad, 'utf-8')), 'localidad');
        #Obligatorio

        #No es necesario rellenarlo
        $this -> edata['motivorechazo'] = null;

        #No es necesario rellenarlo
        $this -> edata['fase'] = null;

        #No es necesario rellenarlo
        $this -> edata['estado'] = null;

        #No es necesario rellenarlo
        $this -> edata['idmodificacion'] = null;

        #No es necesario rellenarlo
        $this -> edata['fechaactualizacion'] = null;

        #No es necesario rellenarlo
        $this -> edata['codentidmodif'] = null;

        $this -> cargarEdata(date('d/m/y H:i:s', $this -> dbedata -> fechainimodalidad), 'fechainimodalidad');
        #Obligatorio

        #No es necesario rellenarlo
        $this -> edata['convresol'] = null;

        #No es necesario rellenarlo
        $this -> edata['fechaconvresol'] = null;

        #No es necesario rellenarlo
        $this -> edata['codentidadintr'] = null;

        #No es necesario rellenarlo
        $this -> edata['idcambioanoacad'] = null;

        #No es necesario rellenarlo
        $this -> edata['idpi'] = null;
    }

    function getNombre() {
        return $this -> data -> fullname;
    }

    function getTutores() {
        return $this -> getParticipantesTipo('T');
    }

    function getCoordinadores() {
        return $this -> getParticipantesTipo('C');
    }

    function getParticipantesTipo($tipo) {
        $this -> getParticipantes();
        $participantes = array();
        foreach($this->participantes as $participante) {
            if($participante -> getTipo() == $tipo)
                $participantes[] = $participante;
        }
        return $participantes;
    }

    function getParticipantes($filter_apto=True) {
        if($this -> dparticipantes)
            return $this -> participantes;
        $participantes = array();
        $userlist = mgm_get_course_participants($this -> data, true);
        if($userlist)
            foreach($userlist as $userdata) {
                $participantes[$userdata -> userid] = new Usuario($userdata, $this, $this->show, $filter_apto);
            }
        //$this -> dparticipantes = True;
        return $participantes;
    }

    function aprobado($usuario) {
    	global $DB;
        if(!$ctask = mgm_get_certification_task($this -> data -> id))
            return false;
        if(!$grade = $DB->get_record('grade_grades', 'itemid', $ctask -> id, 'userid', $usuario -> data -> userid))
            return false;
        return $grade -> finalgrade == $grade -> rawgrademax;
    }

    function getTareas() {
    	global $DB;
        if($this -> tareas)
            return $this -> tareas;
        if($tareas_data = $DB->get_records("feedback", 'course', $this -> data -> id)) {
            foreach($tareas_data as $tarea_data)
                $this -> tareas[] = new Tarea($tarea_data, $this);
            return $this -> tareas;
        } else
            return null;
    }

}

class Usuario {
    var $data;
    var $dbdata;
    var $edata = array();
    var $edatap = array();
    var $curso;
    var $incidencias = array();
    var $info;
    var $apto;
    var $tipoparticipante;
    var $show=array('norol'=>1, 'nodni'=>1, 'usuario'=>1);
    function format_text($str){
    	  if ($str){
    	   return '"'.str_replace('"','',$str).'"';;
    	  }
    	  return $str;
    }

    function set_dbdata($userid){
    	global $DB;
    	$data = $DB->get_record('edicion_user', 'userid', $userid);
    	if (!$data){
    		$data=new stdClass();
    		$data->dni=null;
    	}
    	if (!$data->dni){
    		$puser=profile_user_record($userid);
    		$user = $DB->get_record('user', 'id', $userid);
      		if ($puser->nifniepasaporte){
      			$data->dni=$puser->nifniepasaporte;
      		}else if ($user->idnumber){
      			$data->dni=$user->idnumber;
      		}
      		if (mgm_validate_cif($data->dni)){
      			$data->tipoid='N';
      		}else{
      			$data->tipoid='P';
      		}
    	}
    	$this -> dbdata=$data;
    }

    function Usuario($data, $curso, $show=false, $filter_apto=True) {
    	global $DB;
    	if($show){
    		foreach($show as $k=>$s){
    	  		$this->show[$k]=$s;
    	  	}
    	}
        $this -> data = $data;
        $this -> curso = $curso;
        $this -> apto=$this->curso->aprobado($this);
        $this -> tipoparticipante=$this -> getTipo();
        $this -> info -> nombre = $data -> username;
        $this -> info -> id = $data -> userid;
        $this -> info -> curso = $this -> curso -> data -> fullname;
        $this -> info -> sesskey = $_SESSION['USER'] -> sesskey;
        //$this -> dbdata = get_record('edicion_user', 'userid', $data -> userid);
				$this->set_dbdata($data -> userid);
        //Datos para participantes.csv
        $this -> edata['anoacademico'] = $this->format_text($this -> curso -> edicion -> getAnoAcademico());
        #Obligatorio
        //$this -> edata['anoacademico'] = $this->format_text("20102011");
        $this -> edata['codactividad'] = $this->format_text($this -> curso -> edata['codactividad']);
        #Obligatorio, proviene de la actividad/curso. Nos vendrá de vuelta
        if(!$this -> dbdata) {
            $this -> edata['tipoid'] = null;
            #Obligatorio, "N" o "P" o "T"
            $this -> edata['DNI'] = null;
            #Obligatorio
            $this -> incidencias[] = array('usuario',get_string('incidencia_usuario', 'mgm', $this -> info));

        } else {
        	  if ($filter_apto && $this -> tipoparticipante=='A'){
        	    if((!$this -> dbdata -> dni || strlen($this -> dbdata -> dni) != 9) && ($this->apto ))
                $this -> incidencias[] = array('nodni',get_string('incidencia_dni', 'mgm', $this -> info));
        	  }else{
        	  	if((!$this -> dbdata -> dni || strlen($this -> dbdata -> dni) != 9))
        	  		$this -> incidencias[] = array('nodni',get_string('incidencia_dni', 'mgm', $this -> info));
        	  }
            $this -> edata['tipoid'] = $this->format_text($this -> dbdata -> tipoid);
            $this -> edata['DNI'] = strtoupper($this -> dbdata -> dni);
            $this -> edata['DNI'] = $this->format_text(substr($this -> edata['DNI'], strspn($this -> edata['DNI'], '0')));
        }
        $this -> edata['creditos'] = $this -> curso -> edata['numcreditos'];
        #Obligatorio, proviene de la actividad/curso
        $this -> edata['fechaemision'] = null;
        $this -> edata['fechaultduplicado'] = null;
        $this -> edata['numduplicados'] = null;
        $this -> edata['remitido'] = null;
        $this -> edata['codmotivo'] = null;
        $this -> edata['numregistro'] = null;
        $this -> edata['numregistroCCAA'] = null;
        $this -> edata['generacertif'] = null;
        $this -> edata['codtipoparticipante'] = $this->format_text($this->tipoparticipante);
        #Obligatorio
        if(!$this -> edata['codtipoparticipante'] ){
            $this -> incidencias[] = array('norol',get_string('incidencia_no_tipo_usuario', 'mgm', $this -> info));
        }
        $this -> edata['codmodalidad'] = $this->format_text($this -> curso -> edata['codmodalidad']);
        #Obligatorio, proviene de la actividad/curso
        $this -> edata['fechainicio'] = $this -> curso -> edata['fechainicio'];
        #Obligatorio, proviene de la actividad/curso
        $this -> edata['codtipoactividad'] = $this->format_text('AP');
        #Obligatorio
        $this -> edata['idayuda'] = null;
        $this -> edata['organismo'] = null;
        $this -> edata['impayuda'] = null;
        $this -> edata['codagrupacion'] = $this -> curso -> edata['codagrupacion'];
        #Obligatorio, proviene de la actividad/curso
        $this -> edata['numhoras'] = $this -> curso -> edata['numhoras'];
        #Obligatorio, proviene de la actividad/curso

        //Datos para profesores.csv
        $this -> edatap['tipoid'] = $this->format_text($this -> edata['tipoid']);
        #Obligatorio
        $this -> edatap['DNI'] = $this->format_text($this -> edata['DNI']);
        #Obligatorio
        if(!$this -> dbdata) {
            $this -> edatap['codniveleducativo'] = null;
            #Obligatorio
            $this -> edatap['codcuerpodocente'] = null;
            #Obligatorio
        } else {
            $this -> edatap['codniveleducativo'] =$this->format_text( $this -> dbdata -> codniveleducativo);
            #Obligatorio
            $this -> edatap['codcuerpodocente'] = $this->format_text($this -> dbdata -> codcuerpodocente);
            #Obligatorio
        }
        if(!$this -> dbdata) {
            $this -> edatap['codprovincia'] = null;
            #Obligatorio
            $this -> edatap['codpostal'] = null;
            #Obligatorio
            $this -> edatap['codcentro'] = null;
            #Obligatorio
        } else {
            $this -> edatap['codprovincia'] =$this->format_text( $this -> dbdata -> codprovincia);
            #Obligatorio
            $this -> edatap['codpostal'] =$this->format_text($this -> dbdata -> codpostal);
            #Obligatorio
            $this -> edatap['codcentro'] = $this -> dbdata -> cc;
            #Obligatorio
        }
        $userdata = $DB->get_record('user', 'id', $data -> userid);
        $apellidos = explode(' ',trim($userdata -> lastname), 2);
        if(count($apellidos) > 0)
            $this -> edatap['apellido1'] = $this->format_text(mb_strtoupper($apellidos[0], 'utf-8'));
        #Obligatorio
        else
            $this -> edatap['apellido1'] = null;
        if(count($apellidos) > 1)
            $this -> edatap['apellido2'] = $this->format_text(mb_strtoupper($apellidos[1], 'utf-8'));
        #Obligatorio
        else
            $this -> edatap['apellido2'] = null;
        $this -> edatap['nombre'] = $this->format_text(mb_strtoupper(trim($userdata -> firstname), 'utf-8'));
        #Obligatorio
        $this -> edatap['anosexperiencia'] = null;
        $this -> edatap['situacionadmin'] = null;
        $this -> edatap['domicilio'] = $this->format_text(mb_strtoupper($userdata -> address, 'utf-8'));
        #Obligatorio
        $this -> edatap['telefono'] = $userdata -> phone1;
        #Obligatorio
        $this -> edatap['localidad'] =$this->format_text( mb_strtoupper($userdata -> city, 'utf-8'));
        #Obligatorio
        if(!$this -> dbdata) {
            $this -> edatap['codpais'] = null;
            $this -> edatap['sexo'] = null;
            #Obligatorio
        } else {
            $this -> edatap['codpais'] =$this->format_text( $this -> dbdata -> codpais);
            $this -> edatap['sexo'] = $this->format_text($this -> dbdata -> sexo);
            #Obligatorio
        }
    }

    function getNombre() {
        return $this -> data -> username;
    }

    function getTipo() {
        $roles = mgm_get_certification_roles();
        if($roles) {
            if($this -> data -> roleid == $roles['alumno']) {
                $this -> info -> tipo = 'Alumno';
                return 'A';
            } elseif($this -> data -> roleid == $roles['tutor']) {
                $this -> info -> tipo = 'Tutor';
                return 'T';
            } elseif($this -> data -> roleid == $roles['coordinador']) {
                $this -> info -> tipo = 'Coordinador';
                return 'C';
            }
        }
        return false;
    }

}

class Tarea {
    var $data;
    var $course;

    function Tarea($data, $course) {
    	global $DB;
        $this -> data = $data;
        $module = $DB->get_record('modules', 'name', 'feedback');
        $this -> instancia = $DB->get_record('course_modules', 'course', $course -> data -> id, 'instance', $data -> id, 'module', $module -> id);
        $this -> course = $course;
    }

    function getNombre() {
        return $this -> data -> name;
    }

    function completada($usuario) {
    	global $DB;
        if($DB->get_record('feedback_completed', 'feedback', $this -> data -> id, 'userid', $usuario -> data -> userid))
            return true;
        else
            return false;
    }

}

class EmisionDatos {
    var $edicion;
    var $uexcluidos;
    var $separador_campo = ';';
    var $prefix = 'mgm_export';
    var $show=array('nodni'=>1, 'norol'=> 1, 'usuario'=>1, 'curso'=>1, 'tareas'=>0, 'noapto'=>0);
    var $alumnos_aptos=true;
    var $filter_aptos=true;

    function EmisionDatos($edicion = null, $filter_aptos=True) {
        $this -> edicion = new Edicion($edicion);
        $this -> filter_aptos=$filter_aptos;
    }

    function setLog($show){
    	foreach($show as $k=>$v){
    		$this->show[$k]=$v;
    	}
    }

    function Validar($fechaactual = null) {
        if(!$fechaactual)
            $fechaactual = mktime();

        $cursos = $this -> edicion -> getCursos();

        $tareas_sin_f = array();
        if($cursos && $this->show['tareas'])
            foreach($cursos as $curso) {
                $usuarios = array_merge($curso -> getTutores(), $curso -> getCoordinadores());
                $tareas = $curso -> getTareas();
                if($tareas)
                    foreach($tareas as $tarea) {
                        if($usuarios)
                            foreach($usuarios as $usuario) {
                                if(!$tarea -> completada($usuario)) {
                                    $tarea_sin_f = new stdClass();
                                    $tarea_sin_f -> curso = $curso -> getNombre();
                                    $tarea_sin_f -> usuario = $usuario -> getNombre();
                                    $tarea_sin_f -> tarea = $tarea -> getNombre();
                                    $tarea_sin_f -> tareaid = $tarea -> instancia -> id;
                                    $tarea_sin_f -> tipo = $usuario -> info -> tipo;
                                    $tareas_sin_f[] = $tarea_sin_f;
                                }
                            }
                    }
            }
        $ret = new stdClass();
        $ret -> ok = False;
        $ret -> incidencias = array();
        if($this -> edicion -> getFin() < $fechaactual) {
            if($tareas_sin_f) {
                foreach($tareas_sin_f as $tarea) {
                    $ret -> incidencias[] = get_string('user_no_task_ended', 'mgm', $tarea);
                }
            } else {
                $ret -> ok = True;
            }
        } else {
            $ret -> incidencias[] = get_string('edition_not_ended', 'mgm');
        }
        return $ret;
    }

    function Excluir($usuarios) {
        $this -> uexcluidos = $usuarios;
    }

    function aFichero($directorio) {
        $ret = new stdClass();
        $ret -> ok = True;
        $ret -> incidencias = array();
        $cursos = $this -> edicion -> getCursos($this->show);
        $fparticipantes = fopen("/tmp/participantes.csv", "w");
        $factividades = fopen("/tmp/actividades.csv", "w");
        $fprofesores = fopen("/tmp/profesores.csv", "w");
        $cabecera_participantes = False;
        $cabecera_actividades = False;
        $cabecera_profesores = False;
        $profesores = array();
        $inicio=(int)time();
        if($cursos)
            $i=0;
            foreach($cursos as $curso) {
				$i++;
				$actual=$inicio-(int)time();
                if(!$cabecera_actividades) {
                    fwrite($factividades, '"'. implode('"'. $this -> separador_campo .'"', array_keys($curso -> edata)) . '"'."\n");
                    $cabecera_actividades = True;
                }
                if($curso -> incidencias)
                	$ret -> incidencias = array_merge($ret -> incidencias, $curso -> incidencias);
                else
              		fwrite($factividades, implode($this -> separador_campo, $curso -> edata) . "\n");
                $participantes = $curso -> getParticipantes($this->filter_aptos);
//                $memory=memory_get_usage();
//                print $i.'-'.$curso->data->fullname . '-' . $curso->participantes.' - Mem: '.$memory/(1024*1024).' Tiempo: '.$actual. '<br/>';
                foreach($participantes as $k=>$participante) {
                    if(!$cabecera_participantes) {
                        fwrite($fparticipantes,'"' . implode('"'. $this -> separador_campo . '"', array_keys($participante -> edata)) .'"'."\n");
                        $cabecera_participantes = True;
                    }
                    if($participante -> incidencias){
                    	$tmp_inc=array();
                        foreach($participante -> incidencias as $incidencia){
                        	if ($this->show[$incidencia[0]])
                        		$tmp_inc[]=$incidencia[1];
                        }
                        if ($tmp_inc){
                        	$ret -> incidencias = array_merge($ret -> incidencias, $tmp_inc);
                        }
                	}
                    else{
                    	  $c1=in_array($participante -> tipoparticipante, array('C', 'T')) || $aprobado = $participante->apto;
                    	  $c2=!$aprobado && $participante -> edata['codtipoparticipante'] && $this->show['noapto'];
		                    if($c1) {
		                        if($participante -> dbdata)
		                            $profesores[$participante -> edata['DNI']] = $participante;
		                        fwrite($fparticipantes, implode($this -> separador_campo, $participante -> edata) . "\n");
		                    } elseif($c2)
		                        $ret -> incidencias[] = get_string('incidencia_no_aprobado', 'mgm', $participante -> info);
                    }

                }
                unset($curso);
            }
        if($profesores)
            foreach($profesores as $profesor) {
                if(!$cabecera_profesores) {
                    fwrite($fprofesores, '"' . implode('"'. $this -> separador_campo . '"', array_keys($profesor -> edatap)) . '"'."\n");
                    $cabecera_profesores = True;
                }
                if (! $profesor->incidencias)
                    fwrite($fprofesores, implode($this -> separador_campo, $profesor -> edatap) . "\n");
            }
        fclose($factividades);
        fclose($fparticipantes);
        fclose($fprofesores);
        foreach(glob($directorio."/".$this->prefix."*") as $filename) {
            @unlink($filename);
        }
        $ret -> filename = tempnam($directorio, $this -> prefix) . ".zip";
        zip_files(array("/tmp/participantes.csv", "/tmp/actividades.csv", "/tmp/profesores.csv"), $ret -> filename);
        $newname = $this -> prefix . md5_file($ret -> filename);
        rename($ret -> filename, $directorio . "/" . $newname);
        $ret -> filename = $newname;
        @unlink("/tmp/participantes.csv");
        @unlink("/tmp/actividades.csv");
        @unlink("/tmp/profesores.csv");
        return $ret;
    }

}

class JoinUsers{
		var $user_orig=false;
		var $user_dest=false;
		var $user_diff=null;
		var $rkeys=array('id','userid');//CLAVES QUE NO SE MUESTRAN EN DIFERENCIAS
		var $obj2save=false;
		var $fsave=true; //flag para guardar cambios

		function JoinUsers($orig=false, $dest=false){
			if ($orig){
				$this->user_orig=$orig;

			}
			if ($dest){
				$this->user_dest=$dest;

			}
		}

		function removeRKeys($keys){
			foreach ($this->rkeys as $rkey){
				$key=array_search($rkey, $keys);
				if (is_int($key)){
					unset($keys[$key]);
				}
			}
			return array_values($keys);
		}

		function addDiff(){
			if (!isset($this->user_diff)){
         		if (isset($this->user_orig) and isset($this->user_dest)){
         			$this->user_diff=new stdClass();
         	  		$rkeys=array();
         	  		$keys_orig=array_keys(get_object_vars($this->user_orig));
         	  		$keys_dest=array_keys(get_object_vars($this->user_dest));
         	  		$keys=array_unique(array_merge($keys_orig, $keys_dest));
         	  		$keys=$this->removeRKeys($keys);
         			foreach($keys as $key){
         				if ($this->user_orig->$key != $this->user_dest->$key){
         					$this->user_diff->$key->orig=$this->user_orig->$key;
         					$this->user_diff->$key->dest=$this->user_dest->$key;
         					if (! isset($this->user_dest->$key) || $this->user_dest->$key == ''){
         						$this->user_diff->$key->v=1;
         					}else{
         					  $this->user_diff->$key->v=2;
         					}
         				}
         			}
         		}
			}
		}
		function setDiffField($key, $value=2){
			$this->userdiff->$key->v=$value;
		}

		function setDiffVals($keys2set){
				foreach(array_keys(get_object_vars($this->user_diff)) as $key => $value){
				 	$this->user_diff->$value->v=$keys2set[$key];
				}
		}
		#Listado de valores a ser establecidos en el usuario destino
		function getValues2Modif(){
		  $ret=array();
		  foreach(array_keys(get_object_vars($this->user_diff)) as $key => $value){
				 	if ($this->user_diff->$value->v==1){
				 		$ret[$value]=$this->user_orig->$value;
				 	}
			}
			return $ret;
		}

		function getSaveValues(){
				if (! $this->obj2save){
					$this->setSaveValues();
				}
				return $this->obj2save;
		}

		function setSaveValues(){
				$this->obj2save=new stdClass();
				$this->obj2save->id=$this->user_dest->id;
				foreach ($this->getValues2Modif() as $k=>$v){
					$this->obj2save->$k=$v;
				}
				return true;
		}

		function updateCertDest(){
			global $DB;
			if ($cert_orig = mgm_get_cert_history($this->user_orig->id)){
				foreach  ($cert_orig as $cert){
					$cert->userid=$this->user_dest->id;
					$DB->update_record($cert);
				}
			}
			return true;
		}

		function save(){
				if (! $this->obj2save){
					$this->setSaveValues();
				}
				if ($this->fsave){
					  //guardar destino
						mgm_update_user_complete($this->obj2save);
						//actualizar cursos certificados
						$this->updateCertDest();
						//eliminar usurio origen
						if ($user = get_record('user', 'id', $this->user_orig->id)){
							$ret = delete_user($user);
							//borrar user
						}
					  	//borrar edicion_user
						if (record_exists('edicion_user', 'userid', $this->user_orig->id)){
							$ret = $DB->delete_records('edicion_user', 'userid', $this->user_orig->id);
						}
						//borrar user
						return true;
				}
			  return false;
		}


		function getDiff(){
			return $this->user_diff;
		}

		function setUserId($userid, $type='orig'){
			$type = strtolower($type);
			if ($type == 'orig' or $type == 'dest'){
				$usertype='user_' .$type;
				if ($user=mgm_get_user_complete($userid)){
			  		$this->$usertype=$user;
			  		return true;
				}
				return false;
			}
			else{
				return false;
			}
		}

		function getDiffTable(){
			global $CFG;
			$table=new stdClass();
			$table->head=array(
			'Campo',
			'<a href="'.$CFG->wwwroot.'/user/view.php?id='.$this->user_orig->id.'&amp;course='.SITEID.'">'.fullname($this->user_orig, true).' (id: '.$this->user_orig->id.')</a>',
			'<a href="'.$CFG->wwwroot.'/user/view.php?id='.$this->user_dest->id.'&amp;course='.SITEID.'">'.fullname($this->user_dest, true).' (id: '.$this->user_dest->id.')</a>',
			'Valor a almacenar'
			);
			$table->data=array();
			foreach(array_keys(get_object_vars($this->user_diff)) as $key => $value){
				  if ($this->user_diff->$value->v == 1){
				  	$checked1='checked';
				  	$checked2='';
				  }else{
				  	$checked1='';
				  	$checked2='checked';
				  }
				  $table->data[] = array(
				  mgm_get_string($value),
				 	$this->user_diff->$value->orig,
				  $this->user_diff->$value->dest,
				  '<div align="center">
					<input type="radio" name="' .$key. '" value="1" '.$checked1.'>Origen
					<input type="radio" name="' .$key. '" value="2" '.$checked2.'>Destino
					</div>'

				);
			}
//			$table->data[]=array(
//			get_string('certifications'),
//			mgm_get_cert_history_str($this->user_orig->id),
//			mgm_get_cert_history_str($this->user_dest->id),
//			'Nota'
//			);
			return $table;
		}

		function getSaveTable(){
			global $CFG;
			$table=new stdClass();
			$table->head=array(
			'Número',
			'Campo',
			'Valor'
			);
			$table->data=array();
			$c=1;
			$this->setSaveValues();
			foreach(get_object_vars($this->obj2save) as $key => $value){
				  $table->data[] = array(
				  $c,
				  mgm_get_string($key),
				 	$value,
				);
				$c++;
			}
			return $table;
		}

		function getCertOrigTable(){
			global $CFG;
			if ($cert_orig=mgm_get_cert_history($this->user_orig->id)){
				$table=new stdClass();
				$table->head=array(
				'Número',
				'Curso',
				'Registro',
				'Confirmado'
				);
				$table->data=array();
				$c=1;
				foreach($cert_orig as $cert){
					$course=get_record('course', 'id', $cert->courseid);
					  $table->data[] = array(
				  	$c,
				  	$course->fullname,
				 		$cert->numregistro,
				 		$cert->confirm
					);
					$c++;
			  }
			  return $table;
			}
			return false;
		}

		function getCertTable(){
			global $DB;
			$cert_orig=mgm_get_cert_history($this->user_orig->id);
			$cert_dest=mgm_get_cert_history($this->user_dest->id);
			$table=new stdClass();
			if ($cert_orig || $cert_dest){
				$table->head=array(
				fullname($this->user_orig, true),
				fullname($this->user_dest, true)
				);
				$table->data=array();
				$numrows=count($cert_orig);
				if(count($cert_orig)<count($cert_dest)){
					$numrows=count($cert_dest);
				}
		  	for($i=0;$i<$numrows; $i++){
		  		$orig=array_pop($cert_orig);
		  		$dest=array_pop($cert_dest);
		    	if (! is_null($orig)){
		    		$course = $DB->get_record('course', 'id', $orig->courseid);
		  			$origs='Curso: ' . $course->fullname .', numreg: '. $orig->numregistro . ' confirm: ' . $orig->confirm;
		  		}else{
		  			$orig='';
		  		}
		  		if (! is_null($dest)){
		  			$course = $DB->get_record('course', 'id', $dest->courseid);
		  			$dests='Curso: ' . $course->fullname .', numreg: '. $dest->numregistro . ' confirm: ' . $dest->confirm;
		  		}else{
		  			$dest='';
		  		}
		  		$table->data[]=array($origs, $dests);
		   	}
			}
			return $table;
		}

		function displayDiffForm($table){
			print '<form action="join_users_act.php" method="POST">';
    		print_table($table);
    		print '<input type="hidden" value="1" name="confirm">
    		<input type="hidden" value="'.sesskey().'" name="sesskey">
    		<input type="submit" name="submit_yes" id="Submit_btn1" value="'.get_string('next').'">
    		</form>
    		<form action="join_users.php" method="GET">
    		<input type="submit" name="submit_no" id="Submit_btn2" value="'.get_string('cancel').'">
    		</form>';
		}

		function getCertificateRow(){}
}


class ImportData{
		var $filename=false;
    var $cmdPath='/usr/bin/';
    var $edition=0;

		function ImportData($file=false, $edition=0){
			if ($file){
				$this->setFile($file);
			}
			$this->edition=$edition;
		}

		function setFile($file){
			if (file_exists($file)){
   				$this->filename=$file;
   				return true;
			}
   			return false;
		}

		function getTables(){
			$dev=false;
			if ($this->filename){
        		$cmd=$this->cmdPath."mdb-tables ".$this->filename;
      			$dev=system($cmd, $ret);
			}
			if ($ret!=0){
				$dev=false;
			}else{
				$dev=explode(' ', $dev );
			}
			return $dev;
		}

#return  a handle for manipulate the command output
#if any problem return false.
		function getDataTableHandle($table){
			$dev=false;
			if ($this->filename){
        		$cmd=$this->cmdPath."mdb-export ".$this->filename. ' ' . $table;
      			$dev=popen($cmd, "r");
			}
			return $dev;
		}

	  function setDataHistory(){
	  	$tables=$this->getTables();
	  	if ($tables && array_search('PARTICIPANTES', $tables)!== FALSE){
				$handle=$this->getDataTableHandle('PARTICIPANTES');
				if($handle){
					$head = fgetcsv($handle, 0, ",", '"', '\\');
	  		  while (($reg = fgetcsv($handle, 0, ",", '"', '\\')) !== FALSE) {
			    	$cert_reg=new stdClass();
			    	$cert_reg->userid=0;
			    	$cert_reg->edicionid=$this->edition;
			    	$cert_reg->courseid='0';
			    	$cert_reg->roleid=0;
			    	$cert_reg->numregistro=$reg[10];
			    	$cert_reg->confirm=1;
			    	$cert_reg->tipodocumento='N';
			    	$cert_reg->numdocumento='0';
						if (isset($reg[2])){
							$cert_reg->tipodocumento=$reg[2];
						}
					  if (isset($reg[3])){
						  $cert_reg->userid=mgm_get_userid_from_dni($reg[3]);
						  $cert_reg->numdocumento=$reg[3];
					  }
						if (isset($reg[5])){
							$cert_reg->courseid=$reg[5];
						}
						$dev=mgm_set_cert_history($cert_reg);
						$out=$out.$dev[1];
			    }
				}
	  	}else{
	  		$dev=array(1, 'No se ha encontrado la tabla participantes.');
	  	}
	  	return false;

	  }



}
