Instrucciones para importar datos de Moodle19 en Moodle2.4

Pasos:
  1) Instalar moodle 2.4 en una base de datos inicial. No crear usuarios adicionales. Dejar solo los 2 que se crean por defecto (guest y admin)	
  2) En el mismo servidor de base de datos importar los datos de la base de datos de Moodle19:
    2.1) Crear una nueva base de datos
    2.2) mysql --host=localhost --user=root -p --port=3306 --default-character-set=utf8 --comments mgm19< "/home/jesusjd/formacion_moodle.sql" donde el nuevo nombre de la base de datos que hemos creado en el punto 2.1 es mgm19
  3) Instalar el modulo configurable report
  4) Desempaquetar en el raiz de moodle el modulo mgm
  5) Instalar el modulo mgm con todos sus componentes
  6) Verificar que la instalacion de mgm ha sido correcta
  7) Importar datos de Moodle 1.9 en el nuevo Moodle 2.4
	  7.1) Copiar los script de importacion de datos al servidor de base de datos "mod/mgm/db/import/*"
	  7.2) En el servidor de base de datos editar import.conf y establecer los valores adecuados de configuracion
	  7.3) En el servidor de base de datos comprobar que import_data.sh tiene permisos de ejecucion
	  7.4) Se puede generar el codigo sql que importará los datos sin importar los datos con el parametro 'noexec':
		     
		     jesusjd@Brianne:/var/www/moodle/mod/mgm/db/import$ ./import_data.sh noexec
		   
		   Una vez ejecutado este comando podemos ver en los ficheros .tmp.sql las sentencias sql que se ejecutaran.
		     
	  7.5) En el servidor de base de datos ejecutar el script de importacion de datos:
	
  	     jesusjd@Brianne:/var/www/moodle/mod/mgm/db/import$ ./import_data.sh
  	     
	  7.6) Esperar a que se terminen de importar todos los datos. Acceder a Moodle y comprobar que los datos se importado correctamente.