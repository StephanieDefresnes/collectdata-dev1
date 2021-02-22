<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Lang;
use App\Entity\Score;
use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $lang0 = new Lang();
        $lang0->setLang("ab");
        $lang0->setName("&#1072;&#1191;&#1089;&#1096;&#1241;&#1072;");
        $lang0->setEnglishName("Abkhaz");
        $lang0->setEnabled(0);
        $manager->persist($lang0);

        $lang1 = new Lang();
        $lang1->setLang("aa");
        $lang1->setName("&#65;&#102;&#97;&#114;&#97;&#102;");
        $lang1->setEnglishName("Afar");
        $lang1->setEnabled(0);
        $manager->persist($lang1);

        $lang2 = new Lang();
        $lang2->setLang("af");
        $lang2->setName("&#65;&#102;&#114;&#105;&#107;&#97;&#97;&#110;&#115;");
        $lang2->setEnglishName("Afrikaans");
        $lang2->setEnabled(0);
        $manager->persist($lang2);

        $lang3 = new Lang();
        $lang3->setLang("ak");
        $lang3->setName("&#65;&#107;&#97;&#110;");
        $lang3->setEnglishName("Akan");
        $lang3->setEnabled(0);
        $manager->persist($lang3);

        $lang4 = new Lang();
        $lang4->setLang("sq");
        $lang4->setName("&#103;&#106;&#117;&#104;&#97;&#32;&#115;&#104;&#113;&#105;&#112;&#101;");
        $lang4->setEnglishName("Albanian");
        $lang4->setEnabled(0);
        $manager->persist($lang4);

        $lang5 = new Lang();
        $lang5->setLang("am");
        $lang5->setName("&#4768;&#4635;&#4653;&#4763;");
        $lang5->setEnglishName("Amharic");
        $lang5->setEnabled(0);
        $manager->persist($lang5);

        $lang6 = new Lang();
        $lang6->setLang("ar");
        $lang6->setName("&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;");
        $lang6->setEnglishName("Arabic");
        $lang6->setEnabled(1);
        $manager->persist($lang6);

        $lang7 = new Lang();
        $lang7->setLang("an");
        $lang7->setName("&#97;&#114;&#97;&#103;&#111;&#110;&#233;&#115;");
        $lang7->setEnglishName("Aragonese");
        $lang7->setEnabled(0);
        $manager->persist($lang7);

        $lang8 = new Lang();
        $lang8->setLang("hy");
        $lang8->setName("&#1344;&#1377;&#1397;&#1381;&#1408;&#1381;&#1398;");
        $lang8->setEnglishName("Armenian");
        $lang8->setEnabled(0);
        $manager->persist($lang8);

        $lang9 = new Lang();
        $lang9->setLang("as");
        $lang9->setName("&#2437;&#2488;&#2478;&#2496;&#2479;&#2492;&#2494;");
        $lang9->setEnglishName("Assamese");
        $lang9->setEnabled(0);
        $manager->persist($lang9);

        $lang10 = new Lang();
        $lang10->setLang("av");
        $lang10->setName("&#1084;&#1072;&#1075;&#1216;&#1072;&#1088;&#1091;&#1083;&#32;&#1084;&#1072;&#1094;&#1216;");
        $lang10->setEnglishName("Avaric");
        $lang10->setEnabled(0);
        $manager->persist($lang10);

        $lang11 = new Lang();
        $lang11->setLang("ae");
        $lang11->setName("&#97;&#118;&#101;&#115;&#116;&#97;");
        $lang11->setEnglishName("Avestan");
        $lang11->setEnabled(0);
        $manager->persist($lang11);

        $lang12 = new Lang();
        $lang12->setLang("ay");
        $lang12->setName("&#97;&#121;&#109;&#97;&#114;&#32;&#97;&#114;&#117;");
        $lang12->setEnglishName("Aymara");
        $lang12->setEnabled(0);
        $manager->persist($lang12);

        $lang13 = new Lang();
        $lang13->setLang("az");
        $lang13->setName("&#97;&#122;&#601;&#114;&#98;&#97;&#121;&#99;&#97;&#110;&#32;&#100;&#105;&#108;&#105;");
        $lang13->setEnglishName("Azerbaijani");
        $lang13->setEnabled(0);
        $manager->persist($lang13);

        $lang14 = new Lang();
        $lang14->setLang("bm");
        $lang14->setName("&#98;&#97;&#109;&#97;&#110;&#97;&#110;&#107;&#97;&#110;");
        $lang14->setEnglishName("Bambara");
        $lang14->setEnabled(0);
        $manager->persist($lang14);

        $lang15 = new Lang();
        $lang15->setLang("ba");
        $lang15->setName("&#1073;&#1072;&#1096;&#1185;&#1086;&#1088;&#1090;&#32;&#1090;&#1077;&#1083;&#1077;");
        $lang15->setEnglishName("Bashkir");
        $lang15->setEnabled(0);
        $manager->persist($lang15);

        $lang16 = new Lang();
        $lang16->setLang("eu");
        $lang16->setName("&#101;&#117;&#115;&#107;&#101;&#114;&#97;");
        $lang16->setEnglishName("Basque");
        $lang16->setEnabled(0);
        $manager->persist($lang16);

        $lang17 = new Lang();
        $lang17->setLang("be");
        $lang17->setName("&#1073;&#1077;&#1083;&#1072;&#1088;&#1091;&#1089;&#1082;&#1072;&#1103;&#32;&#1084;&#1086;&#1074;&#1072;");
        $lang17->setEnglishName("Belarusian");
        $lang17->setEnabled(0);
        $manager->persist($lang17);

        $lang18 = new Lang();
        $lang18->setLang("bn");
        $lang18->setName("&#2476;&#2494;&#2434;&#2482;&#2494;");
        $lang18->setEnglishName("Bengali; Bangla");
        $lang18->setEnabled(0);
        $manager->persist($lang18);

        $lang19 = new Lang();
        $lang19->setLang("bh");
        $lang19->setName("&#2349;&#2379;&#2332;&#2346;&#2369;&#2352;&#2368;");
        $lang19->setEnglishName("Bihari");
        $lang19->setEnabled(0);
        $manager->persist($lang19);

        $lang20 = new Lang();
        $lang20->setLang("bi");
        $lang20->setName("&#66;&#105;&#115;&#108;&#97;&#109;&#97;");
        $lang20->setEnglishName("Bislama");
        $lang20->setEnabled(0);
        $manager->persist($lang20);

        $lang21 = new Lang();
        $lang21->setLang("bs");
        $lang21->setName("&#98;&#111;&#115;&#97;&#110;&#115;&#107;&#105;&#32;&#106;&#101;&#122;&#105;&#107;");
        $lang21->setEnglishName("Bosnian");
        $lang21->setEnabled(0);
        $manager->persist($lang21);

        $lang22 = new Lang();
        $lang22->setLang("br");
        $lang22->setName("&#98;&#114;&#101;&#122;&#104;&#111;&#110;&#101;&#103;");
        $lang22->setEnglishName("Breton");
        $lang22->setEnabled(0);
        $manager->persist($lang22);

        $lang23 = new Lang();
        $lang23->setLang("bg");
        $lang23->setName("&#1073;&#1098;&#1083;&#1075;&#1072;&#1088;&#1089;&#1082;&#1080;&#32;&#1077;&#1079;&#1080;&#1082;");
        $lang23->setEnglishName("Bulgarian");
        $lang23->setEnabled(0);
        $manager->persist($lang23);

        $lang24 = new Lang();
        $lang24->setLang("ca");
        $lang24->setName("&#99;&#97;&#116;&#97;&#108;&#224;");
        $lang24->setEnglishName("Catalan; Valencian");
        $lang24->setEnabled(0);
        $manager->persist($lang24);

        $lang25 = new Lang();
        $lang25->setLang("ch");
        $lang25->setName("&#67;&#104;&#97;&#109;&#111;&#114;&#117;");
        $lang25->setEnglishName("Chamorro");
        $lang25->setEnabled(0);
        $manager->persist($lang25);

        $lang26 = new Lang();
        $lang26->setLang("ce");
        $lang26->setName("&#1085;&#1086;&#1093;&#1095;&#1080;&#1081;&#1085;&#32;&#1084;&#1086;&#1090;&#1090;");
        $lang26->setEnglishName("Chechen");
        $lang26->setEnabled(0);
        $manager->persist($lang26);

        $lang27 = new Lang();
        $lang27->setLang("ny");
        $lang27->setName("&#99;&#104;&#105;&#110;&#121;&#97;&#110;&#106;&#97;");
        $lang27->setEnglishName("Chichewa; Chewa; Nyanja");
        $lang27->setEnabled(0);
        $manager->persist($lang27);

        $lang28 = new Lang();
        $lang28->setLang("zh");
        $lang28->setName("&#20013;&#25991;");
        $lang28->setEnglishName("Chinese");
        $lang28->setEnabled(0);
        $manager->persist($lang28);

        $lang29 = new Lang();
        $lang29->setLang("cv");
        $lang29->setName("&#1095;&#1233;&#1074;&#1072;&#1096;&#32;&#1095;&#1239;&#1083;&#1093;&#1080;");
        $lang29->setEnglishName("Chuvash");
        $lang29->setEnabled(0);
        $manager->persist($lang29);

        $lang30 = new Lang();
        $lang30->setLang("kw");
        $lang30->setName("&#75;&#101;&#114;&#110;&#101;&#119;&#101;&#107;");
        $lang30->setEnglishName("Cornish");
        $lang30->setEnabled(0);
        $manager->persist($lang30);

        $lang31 = new Lang();
        $lang31->setLang("co");
        $lang31->setName("&#99;&#111;&#114;&#115;&#117;");
        $lang31->setEnglishName("Corsican");
        $lang31->setEnabled(0);
        $manager->persist($lang31);

        $lang32 = new Lang();
        $lang32->setLang("cr");
        $lang32->setName("&#5312;&#5158;&#5123;&#5421;&#5133;&#5135;&#5155;");
        $lang32->setEnglishName("Cree");
        $lang32->setEnabled(0);
        $manager->persist($lang32);

        $lang33 = new Lang();
        $lang33->setLang("hr");
        $lang33->setName("&#104;&#114;&#118;&#97;&#116;&#115;&#107;&#105;&#32;&#106;&#101;&#122;&#105;&#107;");
        $lang33->setEnglishName("Croatian");
        $lang33->setEnabled(0);
        $manager->persist($lang33);

        $lang34 = new Lang();
        $lang34->setLang("cs");
        $lang34->setName("&#269;&#101;&#353;&#116;&#105;&#110;&#97;");
        $lang34->setEnglishName("Czech");
        $lang34->setEnabled(0);
        $manager->persist($lang34);

        $lang35 = new Lang();
        $lang35->setLang("da");
        $lang35->setName("&#100;&#97;&#110;&#115;&#107;");
        $lang35->setEnglishName("Danish");
        $lang35->setEnabled(0);
        $manager->persist($lang35);

        $lang36 = new Lang();
        $lang36->setLang("dv");
        $lang36->setName("&#1931;&#1960;&#1928;&#1964;&#1920;&#1960;");
        $lang36->setEnglishName("Divehi; Dhivehi; Maldivian;");
        $lang36->setEnabled(0);
        $manager->persist($lang36);

        $lang37 = new Lang();
        $lang37->setLang("nl");
        $lang37->setName("&#78;&#101;&#100;&#101;&#114;&#108;&#97;&#110;&#100;&#115;");
        $lang37->setEnglishName("Dutch");
        $lang37->setEnabled(0);
        $manager->persist($lang37);

        $lang38 = new Lang();
        $lang38->setLang("dz");
        $lang38->setName("&#3938;&#4011;&#3964;&#3908;&#3851;&#3905;");
        $lang38->setEnglishName("Dzongkha");
        $lang38->setEnabled(0);
        $manager->persist($lang38);

        $lang39 = new Lang();
        $lang39->setLang("en");
        $lang39->setName("&#69;&#110;&#103;&#108;&#105;&#115;&#104;");
        $lang39->setEnglishName("English");
        $lang39->setEnabled(1);
        $manager->persist($lang39);

        $lang40 = new Lang();
        $lang40->setLang("eo");
        $lang40->setName("&#69;&#115;&#112;&#101;&#114;&#97;&#110;&#116;&#111;");
        $lang40->setEnglishName("Esperanto");
        $lang40->setEnabled(0);
        $manager->persist($lang40);

        $lang41 = new Lang();
        $lang41->setLang("et");
        $lang41->setName("&#101;&#101;&#115;&#116;&#105;");
        $lang41->setEnglishName("Estonian");
        $lang41->setEnabled(0);
        $manager->persist($lang41);

        $lang42 = new Lang();
        $lang42->setLang("ee");
        $lang42->setName("&#69;&#651;&#101;&#103;&#98;&#101;");
        $lang42->setEnglishName("Ewe");
        $lang42->setEnabled(0);
        $manager->persist($lang42);

        $lang43 = new Lang();
        $lang43->setLang("fo");
        $lang43->setName("&#102;&#248;&#114;&#111;&#121;&#115;&#107;&#116;");
        $lang43->setEnglishName("Faroese");
        $lang43->setEnabled(0);
        $manager->persist($lang43);

        $lang44 = new Lang();
        $lang44->setLang("fj");
        $lang44->setName("&#118;&#111;&#115;&#97;&#32;&#86;&#97;&#107;&#97;&#118;&#105;&#116;&#105;");
        $lang44->setEnglishName("Fijian");
        $lang44->setEnabled(0);
        $manager->persist($lang44);

        $lang45 = new Lang();
        $lang45->setLang("fi");
        $lang45->setName("&#115;&#117;&#111;&#109;&#105;");
        $lang45->setEnglishName("Finnish");
        $lang45->setEnabled(0);
        $manager->persist($lang45);

        $lang46 = new Lang();
        $lang46->setLang("fr");
        $lang46->setName("&#102;&#114;&#97;&#110;&#231;&#97;&#105;&#115;");
        $lang46->setEnglishName("French");
        $lang46->setEnabled(1);
        $manager->persist($lang46);

        $lang47 = new Lang();
        $lang47->setLang("ff");
        $lang47->setName("&#70;&#117;&#108;&#102;&#117;&#108;&#100;&#101;");
        $lang47->setEnglishName("Fula; Fulah; Pulaar; Pular");
        $lang47->setEnabled(0);
        $manager->persist($lang47);

        $lang48 = new Lang();
        $lang48->setLang("gl");
        $lang48->setName("&#103;&#97;&#108;&#101;&#103;&#111;");
        $lang48->setEnglishName("Galician");
        $lang48->setEnabled(0);
        $manager->persist($lang48);

        $lang49 = new Lang();
        $lang49->setLang("ka");
        $lang49->setName("&#4325;&#4304;&#4320;&#4311;&#4323;&#4314;&#4312;");
        $lang49->setEnglishName("Georgian");
        $lang49->setEnabled(0);
        $manager->persist($lang49);

        $lang50 = new Lang();
        $lang50->setLang("de");
        $lang50->setName("&#68;&#101;&#117;&#116;&#115;&#99;&#104;");
        $lang50->setEnglishName("German");
        $lang50->setEnabled(0);
        $manager->persist($lang50);

        $lang51 = new Lang();
        $lang51->setLang("el");
        $lang51->setName("&#949;&#955;&#955;&#951;&#957;&#953;&#954;&#940;");
        $lang51->setEnglishName("Greek, Modern");
        $lang51->setEnabled(0);
        $manager->persist($lang51);

        $lang52 = new Lang();
        $lang52->setLang("gn");
        $lang52->setName("&#65;&#118;&#97;&#241;&#101;&#39;&#7869;");
        $lang52->setEnglishName("Guaraní");
        $lang52->setEnabled(0);
        $manager->persist($lang52);

        $lang53 = new Lang();
        $lang53->setLang("gu");
        $lang53->setName("&#2711;&#2753;&#2716;&#2736;&#2750;&#2724;&#2752;");
        $lang53->setEnglishName("Gujarati");
        $lang53->setEnabled(0);
        $manager->persist($lang53);

        $lang54 = new Lang();
        $lang54->setLang("ht");
        $lang54->setName("&#75;&#114;&#101;&#121;&#242;&#108;&#32;&#97;&#121;&#105;&#115;&#121;&#101;&#110;");
        $lang54->setEnglishName("Haitian; Haitian Creole");
        $lang54->setEnabled(0);
        $manager->persist($lang54);

        $lang55 = new Lang();
        $lang55->setLang("ha");
        $lang55->setName("&#1607;&#1614;&#1608;&#1615;&#1587;&#1614;");
        $lang55->setEnglishName("Hausa");
        $lang55->setEnabled(0);
        $manager->persist($lang55);

        $lang56 = new Lang();
        $lang56->setLang("he");
        $lang56->setName("&#1506;&#1489;&#1512;&#1497;&#1514;");
        $lang56->setEnglishName("Hebrew (modern)");
        $lang56->setEnabled(0);
        $manager->persist($lang56);

        $lang57 = new Lang();
        $lang57->setLang("hz");
        $lang57->setName("&#79;&#116;&#106;&#105;&#104;&#101;&#114;&#101;&#114;&#111;");
        $lang57->setEnglishName("Herero");
        $lang57->setEnabled(0);
        $manager->persist($lang57);

        $lang58 = new Lang();
        $lang58->setLang("hi");
        $lang58->setName("&#2361;&#2367;&#2344;&#2381;&#2342;&#2368;");
        $lang58->setEnglishName("Hindi");
        $lang58->setEnabled(0);
        $manager->persist($lang58);

        $lang59 = new Lang();
        $lang59->setLang("ho");
        $lang59->setName("&#72;&#105;&#114;&#105;&#32;&#77;&#111;&#116;&#117;");
        $lang59->setEnglishName("Hiri Motu");
        $lang59->setEnabled(0);
        $manager->persist($lang59);

        $lang60 = new Lang();
        $lang60->setLang("hu");
        $lang60->setName("&#109;&#97;&#103;&#121;&#97;&#114;");
        $lang60->setEnglishName("Hungarian");
        $lang60->setEnabled(0);
        $manager->persist($lang60);

        $lang61 = new Lang();
        $lang61->setLang("ia");
        $lang61->setName("&#73;&#110;&#116;&#101;&#114;&#108;&#105;&#110;&#103;&#117;&#97;");
        $lang61->setEnglishName("Interlingua");
        $lang61->setEnabled(0);
        $manager->persist($lang61);

        $lang62 = new Lang();
        $lang62->setLang("id");
        $lang62->setName("&#66;&#97;&#104;&#97;&#115;&#97;&#32;&#73;&#110;&#100;&#111;&#110;&#101;&#115;&#105;&#97;");
        $lang62->setEnglishName("Indonesian");
        $lang62->setEnabled(0);
        $manager->persist($lang62);

        $lang63 = new Lang();
        $lang63->setLang("ga");
        $lang63->setName("&#71;&#97;&#101;&#105;&#108;&#103;&#101;");
        $lang63->setEnglishName("Irish");
        $lang63->setEnabled(0);
        $manager->persist($lang63);

        $lang64 = new Lang();
        $lang64->setLang("ig");
        $lang64->setName("&#65;&#115;&#7909;&#115;&#7909;&#32;&#73;&#103;&#98;&#111;");
        $lang64->setEnglishName("Igbo");
        $lang64->setEnabled(0);
        $manager->persist($lang64);

        $lang65 = new Lang();
        $lang65->setLang("ik");
        $lang65->setName("&#73;&#241;&#117;&#112;&#105;&#97;&#113;");
        $lang65->setEnglishName("Inupiaq");
        $lang65->setEnabled(0);
        $manager->persist($lang65);

        $lang66 = new Lang();
        $lang66->setLang("io");
        $lang66->setName("&#73;&#100;&#111;");
        $lang66->setEnglishName("Ido");
        $lang66->setEnabled(0);
        $manager->persist($lang66);

        $lang67 = new Lang();
        $lang67->setLang("is");
        $lang67->setName("&#205;&#115;&#108;&#101;&#110;&#115;&#107;&#97;");
        $lang67->setEnglishName("Icelandic");
        $lang67->setEnabled(0);
        $manager->persist($lang67);

        $lang68 = new Lang();
        $lang68->setLang("it");
        $lang68->setName("&#105;&#116;&#97;&#108;&#105;&#97;&#110;&#111;");
        $lang68->setEnglishName("Italian");
        $lang68->setEnabled(1);
        $manager->persist($lang68);

        $lang69 = new Lang();
        $lang69->setLang("iu");
        $lang69->setName("&#5123;&#5316;&#5251;&#5198;&#5200;&#5222;");
        $lang69->setEnglishName("Inuktitut");
        $lang69->setEnabled(0);
        $manager->persist($lang69);

        $lang70 = new Lang();
        $lang70->setLang("ja");
        $lang70->setName("&#26085;&#26412;&#35486;");
        $lang70->setEnglishName("Japanese");
        $lang70->setEnabled(0);
        $manager->persist($lang70);

        $lang71 = new Lang();
        $lang71->setLang("jv");
        $lang71->setName("&#98;&#97;&#115;&#97;&#32;&#74;&#97;&#119;&#97;");
        $lang71->setEnglishName("Javanese");
        $lang71->setEnabled(0);
        $manager->persist($lang71);

        $lang72 = new Lang();
        $lang72->setLang("kl");
        $lang72->setName("&#107;&#97;&#108;&#97;&#97;&#108;&#108;&#105;&#115;&#117;&#116;");
        $lang72->setEnglishName("Kalaallisut, Greenlandic");
        $lang72->setEnabled(0);
        $manager->persist($lang72);

        $lang73 = new Lang();
        $lang73->setLang("kn");
        $lang73->setName("&#3221;&#3240;&#3277;&#3240;&#3233;");
        $lang73->setEnglishName("Kannada");
        $lang73->setEnabled(0);
        $manager->persist($lang73);

        $lang74 = new Lang();
        $lang74->setLang("kr");
        $lang74->setName("&#75;&#97;&#110;&#117;&#114;&#105;");
        $lang74->setEnglishName("Kanuri");
        $lang74->setEnabled(0);
        $manager->persist($lang74);

        $lang75 = new Lang();
        $lang75->setLang("ks");
        $lang75->setName("&#2325;&#2358;&#2381;&#2350;&#2368;&#2352;&#2368;");
        $lang75->setEnglishName("Kashmiri");
        $lang75->setEnabled(0);
        $manager->persist($lang75);

        $lang76 = new Lang();
        $lang76->setLang("kk");
        $lang76->setName("&#1179;&#1072;&#1079;&#1072;&#1179;");
        $lang76->setEnglishName("Kazakh");
        $lang76->setEnabled(0);
        $manager->persist($lang76);

        $lang77 = new Lang();
        $lang77->setLang("km");
        $lang77->setName("&#6017;&#6098;&#6040;&#6082;&#6042;");
        $lang77->setEnglishName("Khmer");
        $lang77->setEnabled(0);
        $manager->persist($lang77);

        $lang78 = new Lang();
        $lang78->setLang("ki");
        $lang78->setName("&#71;&#297;&#107;&#361;&#121;&#361;");
        $lang78->setEnglishName("Kikuyu, Gikuyu");
        $lang78->setEnabled(0);
        $manager->persist($lang78);

        $lang79 = new Lang();
        $lang79->setLang("rw");
        $lang79->setName("&#73;&#107;&#105;&#110;&#121;&#97;&#114;&#119;&#97;&#110;&#100;&#97;");
        $lang79->setEnglishName("Kinyarwanda");
        $lang79->setEnabled(0);
        $manager->persist($lang79);

        $lang80 = new Lang();
        $lang80->setLang("ky");
        $lang80->setName("&#1050;&#1099;&#1088;&#1075;&#1099;&#1079;&#1095;&#1072;");
        $lang80->setEnglishName("Kyrgyz");
        $lang80->setEnabled(0);
        $manager->persist($lang80);

        $lang81 = new Lang();
        $lang81->setLang("kv");
        $lang81->setName("&#1082;&#1086;&#1084;&#1080;&#32;&#1082;&#1099;&#1074;");
        $lang81->setEnglishName("Komi");
        $lang81->setEnabled(0);
        $manager->persist($lang81);

        $lang82 = new Lang();
        $lang82->setLang("kg");
        $lang82->setName("&#75;&#105;&#75;&#111;&#110;&#103;&#111;");
        $lang82->setEnglishName("Kongo");
        $lang82->setEnabled(0);
        $manager->persist($lang82);

        $lang83 = new Lang();
        $lang83->setLang("ko");
        $lang83->setName("&#54620;&#44397;&#50612;");
        $lang83->setEnglishName("Korean");
        $lang83->setEnabled(0);
        $manager->persist($lang83);

        $lang84 = new Lang();
        $lang84->setLang("ku");
        $lang84->setName("&#75;&#117;&#114;&#100;&#238;");
        $lang84->setEnglishName("Kurdish");
        $lang84->setEnabled(0);
        $manager->persist($lang84);

        $lang85 = new Lang();
        $lang85->setLang("kj");
        $lang85->setName("&#75;&#117;&#97;&#110;&#121;&#97;&#109;&#97;");
        $lang85->setEnglishName("Kwanyama, Kuanyama");
        $lang85->setEnabled(0);
        $manager->persist($lang85);

        $lang86 = new Lang();
        $lang86->setLang("lb");
        $lang86->setName("&#76;&#235;&#116;&#122;&#101;&#98;&#117;&#101;&#114;&#103;&#101;&#115;&#99;&#104;");
        $lang86->setEnglishName("Luxembourgish, Letzeburgesch");
        $lang86->setEnabled(0);
        $manager->persist($lang86);

        $lang87 = new Lang();
        $lang87->setLang("lg");
        $lang87->setName("&#76;&#117;&#103;&#97;&#110;&#100;&#97;");
        $lang87->setEnglishName("Ganda");
        $lang87->setEnabled(0);
        $manager->persist($lang87);

        $lang88 = new Lang();
        $lang88->setLang("li");
        $lang88->setName("&#76;&#105;&#109;&#98;&#117;&#114;&#103;&#115;");
        $lang88->setEnglishName("Limburgish, Limburgan, Limburger");
        $lang88->setEnabled(0);
        $manager->persist($lang88);

        $lang89 = new Lang();
        $lang89->setLang("ln");
        $lang89->setName("&#76;&#105;&#110;&#103;&#225;&#108;&#97;");
        $lang89->setEnglishName("Lingala");
        $lang89->setEnabled(0);
        $manager->persist($lang89);

        $lang90 = new Lang();
        $lang90->setLang("lo");
        $lang90->setName("&#3742;&#3762;&#3754;&#3762;&#3749;&#3762;&#3751;");
        $lang90->setEnglishName("Lao");
        $lang90->setEnabled(0);
        $manager->persist($lang90);

        $lang91 = new Lang();
        $lang91->setLang("lt");
        $lang91->setName("&#108;&#105;&#101;&#116;&#117;&#118;&#105;&#371;&#32;&#107;&#97;&#108;&#98;&#97;");
        $lang91->setEnglishName("Lithuanian");
        $lang91->setEnabled(0);
        $manager->persist($lang91);

        $lang92 = new Lang();
        $lang92->setLang("lu");
        $lang92->setName("&#84;&#115;&#104;&#105;&#108;&#117;&#98;&#97;");
        $lang92->setEnglishName("Luba-Katanga");
        $lang92->setEnabled(0);
        $manager->persist($lang92);

        $lang93 = new Lang();
        $lang93->setLang("lv");
        $lang93->setName("&#108;&#97;&#116;&#118;&#105;&#101;&#353;&#117;&#32;&#118;&#97;&#108;&#111;&#100;&#97;");
        $lang93->setEnglishName("Latvian");
        $lang93->setEnabled(0);
        $manager->persist($lang93);

        $lang94 = new Lang();
        $lang94->setLang("gv");
        $lang94->setName("&#71;&#97;&#101;&#108;&#103;");
        $lang94->setEnglishName("Manx");
        $lang94->setEnabled(0);
        $manager->persist($lang94);

        $lang95 = new Lang();
        $lang95->setLang("mk");
        $lang95->setName("&#1084;&#1072;&#1082;&#1077;&#1076;&#1086;&#1085;&#1089;&#1082;&#1080;&#32;&#1112;&#1072;&#1079;&#1080;&#1082;");
        $lang95->setEnglishName("Macedonian");
        $lang95->setEnabled(0);
        $manager->persist($lang95);

        $lang96 = new Lang();
        $lang96->setLang("mg");
        $lang96->setName("&#102;&#105;&#116;&#101;&#110;&#121;&#32;&#109;&#97;&#108;&#97;&#103;&#97;&#115;&#121;");
        $lang96->setEnglishName("Malagasy");
        $lang96->setEnabled(0);
        $manager->persist($lang96);

        $lang97 = new Lang();
        $lang97->setLang("ms");
        $lang97->setName("&#98;&#97;&#104;&#97;&#115;&#97;&#32;&#77;&#101;&#108;&#97;&#121;&#117;");
        $lang97->setEnglishName("Malay");
        $lang97->setEnabled(0);
        $manager->persist($lang97);

        $lang98 = new Lang();
        $lang98->setLang("ml");
        $lang98->setName("&#3374;&#3378;&#3375;&#3390;&#3379;&#3330;");
        $lang98->setEnglishName("Malayalam");
        $lang98->setEnabled(0);
        $manager->persist($lang98);

        $lang99 = new Lang();
        $lang99->setLang("mt");
        $lang99->setName("&#77;&#97;&#108;&#116;&#105;");
        $lang99->setEnglishName("Maltese");
        $lang99->setEnabled(0);
        $manager->persist($lang99);

        $lang100 = new Lang();
        $lang100->setLang("mi");
        $lang100->setName("&#116;&#101;&#32;&#114;&#101;&#111;&#32;&#77;&#257;&#111;&#114;&#105;");
        $lang100->setEnglishName("Māori");
        $lang100->setEnabled(0);
        $manager->persist($lang100);

        $lang101 = new Lang();
        $lang101->setLang("mr");
        $lang101->setName("&#2350;&#2352;&#2366;&#2336;&#2368;");
        $lang101->setEnglishName("Marathi (Marāṭhī)");
        $lang101->setEnabled(0);
        $manager->persist($lang101);

        $lang102 = new Lang();
        $lang102->setLang("mh");
        $lang102->setName("&#75;&#97;&#106;&#105;&#110;&#32;&#77;&#807;&#97;&#106;&#101;&#316;");
        $lang102->setEnglishName("Marshallese");
        $lang102->setEnabled(0);
        $manager->persist($lang102);

        $lang103 = new Lang();
        $lang103->setLang("mn");
        $lang103->setName("&#1084;&#1086;&#1085;&#1075;&#1086;&#1083;");
        $lang103->setEnglishName("Mongolian");
        $lang103->setEnabled(0);
        $manager->persist($lang103);

        $lang104 = new Lang();
        $lang104->setLang("na");
        $lang104->setName("&#69;&#107;&#97;&#107;&#97;&#105;&#114;&#361;&#32;&#78;&#97;&#111;&#101;&#114;&#111;");
        $lang104->setEnglishName("Nauru");
        $lang104->setEnabled(0);
        $manager->persist($lang104);

        $lang105 = new Lang();
        $lang105->setLang("nv");
        $lang105->setName("&#68;&#105;&#110;&#233;&#32;&#98;&#105;&#122;&#97;&#97;&#100;");
        $lang105->setEnglishName("Navajo, Navaho");
        $lang105->setEnabled(0);
        $manager->persist($lang105);

        $lang106 = new Lang();
        $lang106->setLang("nb");
        $lang106->setName("&#78;&#111;&#114;&#115;&#107;&#32;&#98;&#111;&#107;&#109;&#229;&#108;");
        $lang106->setEnglishName("Norwegian Bokmål");
        $lang106->setEnabled(0);
        $manager->persist($lang106);

        $lang107 = new Lang();
        $lang107->setLang("nd");
        $lang107->setName("&#105;&#115;&#105;&#78;&#100;&#101;&#98;&#101;&#108;&#101;");
        $lang107->setEnglishName("North Ndebele");
        $lang107->setEnabled(0);
        $manager->persist($lang107);

        $lang108 = new Lang();
        $lang108->setLang("ne");
        $lang108->setName("&#2344;&#2375;&#2346;&#2366;&#2354;&#2368;");
        $lang108->setEnglishName("Nepali");
        $lang108->setEnabled(0);
        $manager->persist($lang108);

        $lang109 = new Lang();
        $lang109->setLang("ng");
        $lang109->setName("&#79;&#119;&#97;&#109;&#98;&#111;");
        $lang109->setEnglishName("Ndonga");
        $lang109->setEnabled(0);
        $manager->persist($lang109);

        $lang110 = new Lang();
        $lang110->setLang("nn");
        $lang110->setName("&#78;&#111;&#114;&#115;&#107;&#32;&#110;&#121;&#110;&#111;&#114;&#115;&#107;");
        $lang110->setEnglishName("Norwegian Nynorsk");
        $lang110->setEnabled(0);
        $manager->persist($lang110);

        $lang111 = new Lang();
        $lang111->setLang("no");
        $lang111->setName("&#78;&#111;&#114;&#115;&#107;");
        $lang111->setEnglishName("Norwegian");
        $lang111->setEnabled(0);
        $manager->persist($lang111);

        $lang112 = new Lang();
        $lang112->setLang("ii");
        $lang112->setName("&#41352;&#41760;&#42175;");
        $lang112->setEnglishName("Nuosu");
        $lang112->setEnabled(0);
        $manager->persist($lang112);

        $lang113 = new Lang();
        $lang113->setLang("nr");
        $lang113->setName("&#105;&#115;&#105;&#78;&#100;&#101;&#98;&#101;&#108;&#101;");
        $lang113->setEnglishName("South Ndebele");
        $lang113->setEnabled(0);
        $manager->persist($lang113);

        $lang114 = new Lang();
        $lang114->setLang("oj");
        $lang114->setName("&#5130;&#5314;&#5393;&#5320;&#5167;&#5287;&#5134;&#5328;");
        $lang114->setEnglishName("Ojibwe, Ojibwa");
        $lang114->setEnabled(0);
        $manager->persist($lang114);

        $lang115 = new Lang();
        $lang115->setLang("cu");
        $lang115->setName("&#1129;&#1079;&#1099;&#1082;&#1098;&#32;&#1089;&#1083;&#1086;&#1074;&#1123;&#1085;&#1100;&#1089;&#1082;&#1098;");
        $lang115->setEnglishName("Old Church Slavonic, Church Slavonic, Old Bulgarian");
        $lang115->setEnabled(0);
        $manager->persist($lang115);

        $lang116 = new Lang();
        $lang116->setLang("om");
        $lang116->setName("&#65;&#102;&#97;&#97;&#110;&#32;&#79;&#114;&#111;&#109;&#111;&#111;");
        $lang116->setEnglishName("Oromo");
        $lang116->setEnabled(0);
        $manager->persist($lang116);

        $lang117 = new Lang();
        $lang117->setLang("or");
        $lang117->setName("&#2835;&#2849;&#2876;&#2879;&#2822;");
        $lang117->setEnglishName("Oriya");
        $lang117->setEnabled(0);
        $manager->persist($lang117);

        $lang118 = new Lang();
        $lang118->setLang("os");
        $lang118->setName("&#1080;&#1088;&#1086;&#1085;&#32;&#230;&#1074;&#1079;&#1072;&#1075;");
        $lang118->setEnglishName("Ossetian, Ossetic");
        $lang118->setEnabled(0);
        $manager->persist($lang118);

        $lang119 = new Lang();
        $lang119->setLang("pa");
        $lang119->setName("&#2602;&#2672;&#2588;&#2622;&#2604;&#2624;");
        $lang119->setEnglishName("Panjabi, Punjabi");
        $lang119->setEnabled(0);
        $manager->persist($lang119);

        $lang120 = new Lang();
        $lang120->setLang("pi");
        $lang120->setName("&#2346;&#2366;&#2356;&#2367;");
        $lang120->setEnglishName("Pāli");
        $lang120->setEnabled(0);
        $manager->persist($lang120);

        $lang121 = new Lang();
        $lang121->setLang("fa");
        $lang121->setName("&#1601;&#1575;&#1585;&#1587;&#1740;");
        $lang121->setEnglishName("Persian (Farsi)");
        $lang121->setEnabled(0);
        $manager->persist($lang121);

        $lang122 = new Lang();
        $lang122->setLang("pl");
        $lang122->setName("&#106;&#281;&#122;&#121;&#107;&#32;&#112;&#111;&#108;&#115;&#107;&#105;");
        $lang122->setEnglishName("Polish");
        $lang122->setEnabled(0);
        $manager->persist($lang122);

        $lang123 = new Lang();
        $lang123->setLang("ps");
        $lang123->setName("&#1662;&#1690;&#1578;&#1608;");
        $lang123->setEnglishName("Pashto, Pushto");
        $lang123->setEnabled(0);
        $manager->persist($lang123);

        $lang124 = new Lang();
        $lang124->setLang("pt");
        $lang124->setName("&#112;&#111;&#114;&#116;&#117;&#103;&#117;&#234;&#115;");
        $lang124->setEnglishName("Portuguese");
        $lang124->setEnabled(0);
        $manager->persist($lang124);

        $lang125 = new Lang();
        $lang125->setLang("qu");
        $lang125->setName("&#82;&#117;&#110;&#97;&#32;&#83;&#105;&#109;&#105;");
        $lang125->setEnglishName("Quechua");
        $lang125->setEnabled(0);
        $manager->persist($lang125);

        $lang126 = new Lang();
        $lang126->setLang("rm");
        $lang126->setName("&#114;&#117;&#109;&#97;&#110;&#116;&#115;&#99;&#104;&#32;&#103;&#114;&#105;&#115;&#99;&#104;&#117;&#110;");
        $lang126->setEnglishName("Romansh");
        $lang126->setEnabled(0);
        $manager->persist($lang126);

        $lang127 = new Lang();
        $lang127->setLang("rn");
        $lang127->setName("&#73;&#107;&#105;&#114;&#117;&#110;&#100;&#105;");
        $lang127->setEnglishName("Kirundi");
        $lang127->setEnabled(0);
        $manager->persist($lang127);

        $lang128 = new Lang();
        $lang128->setLang("ro");
        $lang128->setName("&#108;&#105;&#109;&#98;&#97;&#32;&#114;&#111;&#109;&#226;&#110;&#259;");
        $lang128->setEnglishName("Romanian");
        $lang128->setEnabled(0);
        $manager->persist($lang128);

        $lang129 = new Lang();
        $lang129->setLang("ru");
        $lang129->setName("&#1088;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;&#32;&#1103;&#1079;&#1099;&#1082;");
        $lang129->setEnglishName("Russian");
        $lang129->setEnabled(0);
        $manager->persist($lang129);

        $lang130 = new Lang();
        $lang130->setLang("sa");
        $lang130->setName("&#2360;&#2306;&#2360;&#2381;&#2325;&#2371;&#2340;&#2350;&#2381;");
        $lang130->setEnglishName("Sanskrit (Saṁskṛta)");
        $lang130->setEnabled(0);
        $manager->persist($lang130);

        $lang131 = new Lang();
        $lang131->setLang("sc");
        $lang131->setName("&#115;&#97;&#114;&#100;&#117;");
        $lang131->setEnglishName("Sardinian");
        $lang131->setEnabled(0);
        $manager->persist($lang131);

        $lang132 = new Lang();
        $lang132->setLang("sd");
        $lang132->setName("&#2360;&#2367;&#2344;&#2381;&#2343;&#2368;");
        $lang132->setEnglishName("Sindhi");
        $lang132->setEnabled(0);
        $manager->persist($lang132);

        $lang133 = new Lang();
        $lang133->setLang("se");
        $lang133->setName("&#68;&#97;&#118;&#118;&#105;&#115;&#225;&#109;&#101;&#103;&#105;&#101;&#108;&#108;&#97;");
        $lang133->setEnglishName("Northern Sami");
        $lang133->setEnabled(0);
        $manager->persist($lang133);

        $lang134 = new Lang();
        $lang134->setLang("sm");
        $lang134->setName("&#103;&#97;&#103;&#97;&#110;&#97;&#32;&#102;&#97;&#39;&#97;&#32;&#83;&#97;&#109;&#111;&#97;");
        $lang134->setEnglishName("Samoan");
        $lang134->setEnabled(0);
        $manager->persist($lang134);

        $lang135 = new Lang();
        $lang135->setLang("sg");
        $lang135->setName("&#121;&#226;&#110;&#103;&#226;&#32;&#116;&#238;&#32;&#115;&#228;&#110;&#103;&#246;");
        $lang135->setEnglishName("Sango");
        $lang135->setEnabled(0);
        $manager->persist($lang135);

        $lang136 = new Lang();
        $lang136->setLang("sr");
        $lang136->setName("&#1089;&#1088;&#1087;&#1089;&#1082;&#1080;&#32;&#1112;&#1077;&#1079;&#1080;&#1082;");
        $lang136->setEnglishName("Serbian");
        $lang136->setEnabled(0);
        $manager->persist($lang136);

        $lang137 = new Lang();
        $lang137->setLang("gd");
        $lang137->setName("&#71;&#224;&#105;&#100;&#104;&#108;&#105;&#103;");
        $lang137->setEnglishName("Scottish Gaelic; Gaelic");
        $lang137->setEnabled(0);
        $manager->persist($lang137);

        $lang138 = new Lang();
        $lang138->setLang("sn");
        $lang138->setName("&#99;&#104;&#105;&#83;&#104;&#111;&#110;&#97;");
        $lang138->setEnglishName("Shona");
        $lang138->setEnabled(0);
        $manager->persist($lang138);

        $lang139 = new Lang();
        $lang139->setLang("si");
        $lang139->setName("&#3523;&#3538;&#3458;&#3524;&#3517;");
        $lang139->setEnglishName("Sinhala, Sinhalese");
        $lang139->setEnabled(0);
        $manager->persist($lang139);

        $lang140 = new Lang();
        $lang140->setLang("sk");
        $lang140->setName("&#115;&#108;&#111;&#118;&#101;&#110;&#269;&#105;&#110;&#97;");
        $lang140->setEnglishName("Slovak");
        $lang140->setEnabled(0);
        $manager->persist($lang140);

        $lang141 = new Lang();
        $lang141->setLang("sl");
        $lang141->setName("&#115;&#108;&#111;&#118;&#101;&#110;&#115;&#107;&#105;&#32;&#106;&#101;&#122;&#105;&#107;");
        $lang141->setEnglishName("Slovene");
        $lang141->setEnabled(0);
        $manager->persist($lang141);

        $lang142 = new Lang();
        $lang142->setLang("so");
        $lang142->setName("&#83;&#111;&#111;&#109;&#97;&#97;&#108;&#105;&#103;&#97;");
        $lang142->setEnglishName("Somali");
        $lang142->setEnabled(0);
        $manager->persist($lang142);

        $lang143 = new Lang();
        $lang143->setLang("st");
        $lang143->setName("&#83;&#101;&#115;&#111;&#116;&#104;&#111;");
        $lang143->setEnglishName("Southern Sotho");
        $lang143->setEnabled(0);
        $manager->persist($lang143);

        $lang144 = new Lang();
        $lang144->setLang("az");
        $lang144->setName("&#1578;&#1608;&#1585;&#1705;&#1580;&#1607;&#8206;");
        $lang144->setEnglishName("South Azerbaijani");
        $lang144->setEnabled(0);
        $manager->persist($lang144);

        $lang145 = new Lang();
        $lang145->setLang("es");
        $lang145->setName("&#101;&#115;&#112;&#97;&#241;&#111;&#108;");
        $lang145->setEnglishName("Spanish");
        $lang145->setEnabled(1);
        $manager->persist($lang145);

        $lang146 = new Lang();
        $lang146->setLang("su");
        $lang146->setName("&#66;&#97;&#115;&#97;&#32;&#83;&#117;&#110;&#100;&#97;");
        $lang146->setEnglishName("Sundanese");
        $lang146->setEnabled(0);
        $manager->persist($lang146);

        $lang147 = new Lang();
        $lang147->setLang("sw");
        $lang147->setName("&#75;&#105;&#115;&#119;&#97;&#104;&#105;&#108;&#105;");
        $lang147->setEnglishName("Swahili");
        $lang147->setEnabled(0);
        $manager->persist($lang147);

        $lang148 = new Lang();
        $lang148->setLang("ss");
        $lang148->setName("&#83;&#105;&#83;&#119;&#97;&#116;&#105;");
        $lang148->setEnglishName("Swati");
        $lang148->setEnabled(0);
        $manager->persist($lang148);

        $lang149 = new Lang();
        $lang149->setLang("sv");
        $lang149->setName("&#83;&#118;&#101;&#110;&#115;&#107;&#97;");
        $lang149->setEnglishName("Swedish");
        $lang149->setEnabled(0);
        $manager->persist($lang149);

        $lang150 = new Lang();
        $lang150->setLang("ta");
        $lang150->setName("&#2980;&#2990;&#3007;&#2996;&#3021;");
        $lang150->setEnglishName("Tamil");
        $lang150->setEnabled(0);
        $manager->persist($lang150);

        $lang151 = new Lang();
        $lang151->setLang("te");
        $lang151->setName("&#3108;&#3142;&#3122;&#3137;&#3095;&#3137;");
        $lang151->setEnglishName("Telugu");
        $lang151->setEnabled(0);
        $manager->persist($lang151);

        $lang152 = new Lang();
        $lang152->setLang("tg");
        $lang152->setName("&#1090;&#1086;&#1207;&#1080;&#1082;&#1251;");
        $lang152->setEnglishName("Tajik");
        $lang152->setEnabled(0);
        $manager->persist($lang152);

        $lang153 = new Lang();
        $lang153->setLang("th");
        $lang153->setName("&#3652;&#3607;&#3618;");
        $lang153->setEnglishName("Thai");
        $lang153->setEnabled(0);
        $manager->persist($lang153);

        $lang154 = new Lang();
        $lang154->setLang("ti");
        $lang154->setName("&#4725;&#4877;&#4653;&#4763;");
        $lang154->setEnglishName("Tigrinya");
        $lang154->setEnabled(0);
        $manager->persist($lang154);

        $lang155 = new Lang();
        $lang155->setLang("bo");
        $lang155->setName("&#3926;&#3964;&#3921;&#3851;&#3937;&#3954;&#3906;");
        $lang155->setEnglishName("Tibetan");
        $lang155->setEnabled(0);
        $manager->persist($lang155);

        $lang156 = new Lang();
        $lang156->setLang("tk");
        $lang156->setName("&#84;&#252;&#114;&#107;&#109;&#101;&#110;");
        $lang156->setEnglishName("Turkmen");
        $lang156->setEnabled(0);
        $manager->persist($lang156);

        $lang157 = new Lang();
        $lang157->setLang("tl");
        $lang157->setName("&#87;&#105;&#107;&#97;&#110;&#103;&#32;&#84;&#97;&#103;&#97;&#108;&#111;&#103;");
        $lang157->setEnglishName("Tagalog");
        $lang157->setEnabled(0);
        $manager->persist($lang157);

        $lang158 = new Lang();
        $lang158->setLang("tn");
        $lang158->setName("&#83;&#101;&#116;&#115;&#119;&#97;&#110;&#97;");
        $lang158->setEnglishName("Tswana");
        $lang158->setEnabled(0);
        $manager->persist($lang158);

        $lang159 = new Lang();
        $lang159->setLang("to");
        $lang159->setName("&#102;&#97;&#107;&#97;&#32;&#84;&#111;&#110;&#103;&#97;");
        $lang159->setEnglishName("Tonga (Tonga Islands)");
        $lang159->setEnabled(0);
        $manager->persist($lang159);

        $lang160 = new Lang();
        $lang160->setLang("tr");
        $lang160->setName("&#84;&#252;&#114;&#107;&#231;&#101;");
        $lang160->setEnglishName("Turkish");
        $lang160->setEnabled(1);
        $manager->persist($lang160);

        $lang161 = new Lang();
        $lang161->setLang("ts");
        $lang161->setName("&#88;&#105;&#116;&#115;&#111;&#110;&#103;&#97;");
        $lang161->setEnglishName("Tsonga");
        $lang161->setEnabled(0);
        $manager->persist($lang161);

        $lang162 = new Lang();
        $lang162->setLang("tt");
        $lang162->setName("&#1090;&#1072;&#1090;&#1072;&#1088;&#32;&#1090;&#1077;&#1083;&#1077;");
        $lang162->setEnglishName("Tatar");
        $lang162->setEnabled(0);
        $manager->persist($lang162);

        $lang163 = new Lang();
        $lang163->setLang("tw");
        $lang163->setName("&#84;&#119;&#105;");
        $lang163->setEnglishName("Twi");
        $lang163->setEnabled(0);
        $manager->persist($lang163);

        $lang164 = new Lang();
        $lang164->setLang("ty");
        $lang164->setName("&#82;&#101;&#111;&#32;&#84;&#97;&#104;&#105;&#116;&#105;");
        $lang164->setEnglishName("Tahitian");
        $lang164->setEnabled(0);
        $manager->persist($lang164);

        $lang165 = new Lang();
        $lang165->setLang("ug");
        $lang165->setName("&#85;&#121;&#419;&#117;&#114;&#113;&#601;");
        $lang165->setEnglishName("Uyghur, Uighur");
        $lang165->setEnabled(0);
        $manager->persist($lang165);

        $lang166 = new Lang();
        $lang166->setLang("uk");
        $lang166->setName("&#1091;&#1082;&#1088;&#1072;&#1111;&#1085;&#1089;&#1100;&#1082;&#1072;&#32;&#1084;&#1086;&#1074;&#1072;");
        $lang166->setEnglishName("Ukrainian");
        $lang166->setEnabled(0);
        $manager->persist($lang166);

        $lang167 = new Lang();
        $lang167->setLang("ur");
        $lang167->setName("&#1575;&#1585;&#1583;&#1608;");
        $lang167->setEnglishName("Urdu");
        $lang167->setEnabled(0);
        $manager->persist($lang167);

        $lang168 = new Lang();
        $lang168->setLang("uz");
        $lang168->setName("&#79;&#8216;&#122;&#98;&#101;&#107;");
        $lang168->setEnglishName("Uzbek");
        $lang168->setEnabled(0);
        $manager->persist($lang168);

        $lang169 = new Lang();
        $lang169->setLang("ve");
        $lang169->setName("&#84;&#115;&#104;&#105;&#118;&#101;&#110;&#7699;&#97;");
        $lang169->setEnglishName("Venda");
        $lang169->setEnabled(0);
        $manager->persist($lang169);

        $lang170 = new Lang();
        $lang170->setLang("vi");
        $lang170->setName("&#84;&#105;&#7871;&#110;&#103;&#32;&#86;&#105;&#7879;&#116;");
        $lang170->setEnglishName("Vietnamese");
        $lang170->setEnabled(0);
        $manager->persist($lang170);

        $lang171 = new Lang();
        $lang171->setLang("vo");
        $lang171->setName("&#86;&#111;&#108;&#97;&#112;&#252;&#107;");
        $lang171->setEnglishName("Volapük");
        $lang171->setEnabled(0);
        $manager->persist($lang171);

        $lang172 = new Lang();
        $lang172->setLang("wa");
        $lang172->setName("&#119;&#97;&#108;&#111;&#110;");
        $lang172->setEnglishName("Walloon");
        $lang172->setEnabled(0);
        $manager->persist($lang172);

        $lang173 = new Lang();
        $lang173->setLang("cy");
        $lang173->setName("&#67;&#121;&#109;&#114;&#97;&#101;&#103;");
        $lang173->setEnglishName("Welsh");
        $lang173->setEnabled(0);
        $manager->persist($lang173);

        $lang174 = new Lang();
        $lang174->setLang("wo");
        $lang174->setName("&#87;&#111;&#108;&#108;&#111;&#102;");
        $lang174->setEnglishName("Wolof");
        $lang174->setEnabled(0);
        $manager->persist($lang174);

        $lang175 = new Lang();
        $lang175->setLang("fy");
        $lang175->setName("&#70;&#114;&#121;&#115;&#107;");
        $lang175->setEnglishName("Western Frisian");
        $lang175->setEnabled(0);
        $manager->persist($lang175);

        $lang176 = new Lang();
        $lang176->setLang("xh");
        $lang176->setName("&#105;&#115;&#105;&#88;&#104;&#111;&#115;&#97;");
        $lang176->setEnglishName("Xhosa");
        $lang176->setEnabled(0);
        $manager->persist($lang176);

        $lang177 = new Lang();
        $lang177->setLang("yi");
        $lang177->setName("&#1497;&#1497;&#1460;&#1491;&#1497;&#1513;");
        $lang177->setEnglishName("Yiddish");
        $lang177->setEnabled(0);
        $manager->persist($lang177);

        $lang178 = new Lang();
        $lang178->setLang("yo");
        $lang178->setName("&#89;&#111;&#114;&#249;&#98;&#225;");
        $lang178->setEnglishName("Yoruba");
        $lang178->setEnabled(0);
        $manager->persist($lang178);

        $lang179 = new Lang();
        $lang179->setLang("za");
        $lang179->setName("&#83;&#97;&#623;&#32;&#99;&#117;&#101;&#331;&#389;");
        $lang179->setEnglishName("Zhuang, Chuang");
        $lang179->setEnabled(0);
        $manager->persist($lang179);

        $lang180 = new Lang();
        $lang180->setLang("zu");
        $lang180->setName("&#105;&#115;&#105;&#90;&#117;&#108;&#117;");
        $lang180->setEnglishName("Zulu");
        $lang180->setEnabled(0);
        $manager->persist($lang180);

        $status1 = new Status();
        $status1->setName("writing");
        $manager->persist($status1);

        $status2 = new Status();
        $status2->setName("validation");
        $manager->persist($status2);

        $status3 = new Status();
        $status3->setName("validated");
        $manager->persist($status3);

        $status4 = new Status();
        $status4->setName("refused");
        $manager->persist($status4);

        $event1 = new Event();
        $event1->setTitle("Épidémie - Virus aéroporté");
        $event1->setValidated(1);
        $event1->setLangId(47);
        $manager->persist($event1);

        $event2 = new Event();
        $event2->setTitle("Séisme");
        $event2->setValidated(1);
        $event2->setLangId(47);
        $manager->persist($event2);

        $event3 = new Event();
        $event3->setTitle("Innondation");
        $event3->setValidated(1);
        $event3->setLangId(47);
        $manager->persist($event3);

        $event4 = new Event();
        $event4->setTitle("Epidemic - Airborne virus");
        $event4->setValidated(1);
        $event4->setLangId(40);
        $manager->persist($event4);

        $event5 = new Event();
        $event5->setTitle("Earthquake");
        $event5->setValidated(1);
        $event5->setLangId(40);
        $manager->persist($event5);

        $event6 = new Event();
        $event6->setTitle("Flood");
        $event6->setValidated(1);
        $event6->setLangId(40);
        $manager->persist($event6);

        $event7 = new Event();
        $event7->setTitle("Epidemia - Virus en el aire");
        $event7->setValidated(1);
        $event7->setLangId(146);
        $manager->persist($event7);

        $event8 = new Event();
        $event8->setTitle("Terremoto");
        $event8->setValidated(1);
        $event8->setLangId(146);
        $manager->persist($event8);

        $event9 = new Event();
        $event9->setTitle("Inundar");
        $event9->setValidated(1);
        $event9->setLangId(146);
        $manager->persist($event9);
        
        $manager->flush();
    }
}
