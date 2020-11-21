<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:import href="../@layout.xsl"/>
	<xsl:output method="html" encoding="utf-8"/>

	<xsl:template name="content">
		<section class="content is-desktop is-vcentered is-centered">
			<xsl:apply-templates select="fact"/>
		</section>

	</xsl:template>

	<xsl:template match="fact">
		<div class="column has-text-centered">
			<h2><xsl:value-of select="text"/></h2>
			<xsl:apply-templates select="sources"/>
			<xsl:apply-templates select="tags"/>
			<hr/>
			<a href="/fakt/{nextId}" title="Další" id="next">
				<i class="fas fa-chevron-right fa-lg"/>
			</a>
		</div>
	</xsl:template>

	<xsl:template match="tags">
		<div class="pt-2">
			<xsl:apply-templates select="tag"/>
		</div>
	</xsl:template>

	<xsl:template match="tag">
		<span class="has-text-grey is-size-6 p-1">
			#<xsl:value-of select="text()"/>
		</span>
	</xsl:template>

	<xsl:template match="source">
		<a href="{url}" target="_blank" rel="noopener" class="p-1" title="{name}">
			<i class="{icon}"/>
		</a>
	</xsl:template>

</xsl:stylesheet>
