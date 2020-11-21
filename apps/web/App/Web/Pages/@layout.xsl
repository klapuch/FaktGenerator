<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="utf-8"/>

	<xsl:template match="assets/css">
		<link rel="stylesheet" href="{text()}" type="text/css"/>
	</xsl:template>

	<xsl:template match="assets/js">
		<script type="text/javascript" src="{text()}"/>
	</xsl:template>

	<xsl:template match="web">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<html lang="en-US">
			<head>
				<title>
					<xsl:value-of select="page/@title"/>
					<xsl:choose>
						<xsl:when test="page/@title">
							<xsl:text> | FaktGenerator</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>FaktGenerator</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</title>

				<xsl:if test="page/@description">
					<meta name="description" content="{page/@description}"/>
				</xsl:if>

				<xsl:apply-templates select="layout/assets/css"/>
				<xsl:apply-templates select="layout/assets/js"/>
			</head>
			<body>
				<xsl:apply-templates select="page"/>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="page">
		<div class="container is-max-widescreen mt-5 mb-5">
			<xsl:call-template name="content"/>
		</div>
	</xsl:template>

</xsl:stylesheet>
