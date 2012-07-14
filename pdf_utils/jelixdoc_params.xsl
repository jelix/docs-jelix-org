<?xml version='1.0' encoding="iso-8859-1"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

<!-- Exemple pour jelix  --> 

<!-- Options passees a documentclass (11pt par défaut. possible 9 a 12pt) --> 
<xsl:param name="latex.class.options">11pt</xsl:param>

<!-- Profondeur de la numerotation des titre --> 
<xsl:param name="doc.section.depth">2</xsl:param>

<!-- Profondeur de la table des matieres --> 
<xsl:param name="toc.section.depth">1</xsl:param>

<!-- Pour afficher les liste de table, de figure, ... : je veux rien ! --> 
<!-- <xsl:param name="doc.lot.show">figure,table,example</xsl:param> --> 
<xsl:param name="doc.lot.show"></xsl:param>

<!-- Pour masquer ou montrer la table des collaborateur --> 
<!-- Peut aussi etre inhibe dans le fichier de style latex --> 
<!-- <xsl:param name="doc.collab.show">0</xsl:param> --> 


<!-- Autres exemple tire's de la doc docbook  --> 

<!-- The TOC links in the titles, and in blue. -->
<!--<xsl:param name="latex.hyperparam"
  >colorlinks,linkcolor=red,pdfstartview=FitH</xsl:param>-->

<!-- Put the dblatex logo -->
<!-- <xsl:param name="doc.publisher.show">1</xsl:param> --> 

<!-- Options used for documentclass -->
<!--
<xsl:param name="latex.class.options">a4paper,11pt,twoside,openright
</xsl:param>
-->

</xsl:stylesheet>
