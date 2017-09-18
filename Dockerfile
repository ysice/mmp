FROM 		hub.c.163.com/library/tomcat:latest

MAINTAINER 	Amjad Afanah (amjad@dchq.io)

COPY 		./software/ /usr/local/tomcat/webapps/

EXPOSE 80
