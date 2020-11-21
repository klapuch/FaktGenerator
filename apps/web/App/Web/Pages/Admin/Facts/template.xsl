<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
				xmlns:php="http://php.net/xsl" xmlns:xs="http://www.w3.org/1999/XSL/Transform">

	<xsl:import href="../../@layout.xsl"/>
	<xsl:output method="html" encoding="utf-8"/>

	<xsl:template name="content">
		<table class="table is-bordered is-striped is-hoverable is-fullwidth">
			<thead>
				<tr>
					<td><strong>Text</strong></td>
					<td><strong>Tags</strong><xsl:text> </xsl:text><em class="is-size-7">(max. 2)</em></td>
					<td><strong>Sources</strong></td>
					<td><strong>Created at</strong></td>
					<td><strong>Visited count</strong></td>
					<td><strong>Action</strong></td>
				</tr>
			</thead>
			<tbody>
				<xsl:apply-templates select="facts"/>
			</tbody>
		</table>

		<nav class="pagination" role="navigation" aria-label="pagination">
			<xsl:element name="a">
				<xsl:attribute name="class">pagination-previous</xsl:attribute>
				<xsl:if test="//paginator/@page = 1">
					<xsl:attribute name="disabled">true</xsl:attribute>
				</xsl:if>
				<xsl:attribute name="href">
					<xsl:choose>
						<xsl:when test="//paginator/@page = 2">/admin/facts</xsl:when>
						<xsl:otherwise>
							<xsl:text>?page=</xsl:text>
							<xsl:value-of select="//paginator/@page - 1"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
				Previous
			</xsl:element>

			<xsl:element name="a">
				<xsl:attribute name="class">pagination-next</xsl:attribute>
				<xsl:if test="count(facts/fact) &lt;= number(//paginator/@perPage)">
					<xsl:attribute name="disabled">true</xsl:attribute>
				</xsl:if>
				<xsl:attribute name="href">
					<xsl:text>?page=</xsl:text>
					<xsl:value-of select="//paginator/@page + 1"/>
				</xsl:attribute>
				Next
			</xsl:element>
		</nav>
	</xsl:template>

	<xsl:template match="facts">
		<xsl:apply-templates select="fact[position() &lt;= number(//paginator/@perPage)]"/>
	</xsl:template>

	<xsl:template match="fact">
		<form method="POST" action="/admin/fact/delete" id="{id}">
			<input type="hidden" name="id" value="{id}"/>
		</form>

		<tr>
			<xsl:element name="td">
				<xsl:attribute name="title"><xsl:value-of select="text"/></xsl:attribute>
				<xsl:apply-templates select="text">
					<xsl:with-param name="max-length">30</xsl:with-param>
				</xsl:apply-templates>
			</xsl:element>
			<td><xsl:apply-templates select="tags"/></td>
			<td><xsl:apply-templates select="sources"/></td>
			<td><xsl:value-of select="php:function('date', 'j.n.Y H:i', number(created_at))"/></td>
			<td><xsl:value-of select="visited_count"/></td>
			<td>
				<div class="buttons are-small">
					<a title="Edit" class="button is-primary" href="/admin/fact/{id}/edit"><i class="fa fa-edit"/></a>
					<button type="submit" form="{id}" title="Delete" class="button is-danger"><i class="fa fa-trash"/></button>
				</div>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="text">
		<xsl:param name="max-length"/>
		<xsl:choose>
			<xsl:when test="string-length(.) > $max-length and substring(., $max-length + 1) != '.'">
				<xsl:value-of select="substring(., 1, $max-length)"/>
				<xsl:text>...</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="."/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="tag">
		#<xsl:value-of select="text()"/>
	</xsl:template>

	<xsl:template match="source">
		<a href="{url}" target="_blank" rel="noopener nofollow" class="p-1" title="{name} [{url}]">
			<i class="{icon}"/>
		</a>
	</xsl:template>

</xsl:stylesheet>
