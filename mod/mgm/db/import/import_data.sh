#!/bin/sh


echo "Importador de datos (Moodle19 -> Moodle24)"

. ./import.conf
tpl_file=import_users.template.sql
tmp_file=import_users.tmp.sql

sed "s/mgm19/$sour/" $tpl_file > $tmp_file
sed -i "s/mgm24/$dest/" $tmp_file
sed -i "s/mdl_/$prefix/" $tmp_file

comand="mysql --verbose --host=$host --user=$user --port=$port --default-character-set=$charset --comments $dest < $tmp_file"

help(){
		echo "Configure options in import.conf"
		echo "Usage: $0   -> import data"
		echo "Usage: $0  help -> show this help"
		echo "Usage: $0  noexec -> generate file $tmpfile who has sql sentences. No import data. Its only for test sql"
		exit 1	
}

if [ $# -eq 1 ]
then
	if [ $1 = 'noexec' ]
	then		
		echo "noexec"
		echo $comand
		exit 0
	elif [ $1 = 'help' ]
	then
		help
	else		
		echo "Unknow option!"
		help
	fi		
elif [ $# -eq 0 ]
then
	echo "exec"
	echo "Data base password for user $user:"
	read password
	mysql --verbose --host=$host --user=$user --password=$password --port=$port --default-character-set=$charset --comments $dest < $tmp_file
	exit 0	
else
	echo "Unknow option!"
	help		
fi
