<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="html" encoding="utf-8"/>

	<xsl:template match="logs">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<html lang="en-US">
			<body>
				<ul>
					<xsl:apply-templates/>
				</ul>
			</body>
		</html>
	</xsl:template>

	<xsl:template match="log">
		<li>
			<strong>
				<xsl:value-of select="@timestamp"/>
			</strong>

			<xsl:text>(</xsl:text>
			<xsl:value-of select="@type"/>
			<xsl:if test="@level">
				<xsl:text> </xsl:text>
				<xsl:value-of select="@level"/>
			</xsl:if>
			<xsl:text>)</xsl:text>

			<xsl:if test="@filename">
				<br/>
				<xsl:text>[</xsl:text>
				<xsl:value-of select="@filename"/>
				<xsl:text>]</xsl:text>
				<xsl:value-of select="text()"/>
			</xsl:if>
		</li>
	</xsl:template>

</xsl:stylesheet>
