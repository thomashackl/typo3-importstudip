<?xml version="1.0" encoding="ISO-8859-1"?>

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/TR/REC-html40">

    <xsl:output method="xml" version="1.0" encoding="utf-8" standalone="yes"/>

    <!-- Root template, generates FlexForm structure. -->
    <xsl:template match="/">
        <T3FlexForms xmlns="http://www.w3.org/TR/REC-html40">
            <data>
                <xsl:apply-templates/>
            </data>
        </T3FlexForms>
    </xsl:template>

    <!-- Parse old "sheet" element and generate new structure -->
    <xsl:template match="sheet">
        <xsl:if test="@index = 'sDEF'">
            <sheet index="dataSheet">
                <xsl:apply-templates/>
            </sheet>
        </xsl:if>
    </xsl:template>

    <!-- "language" element -->
    <xsl:template match="language">
        <xsl:if test="@index = 'lDEF'">
            <language index="lDEF">
                <xsl:apply-templates select="field[@index = 'contentType']"/>
            </language>
        </xsl:if>
    </xsl:template>

    <!-- Iterate over field elements -->
    <xsl:template match="field[@index = 'contentType']">
        <xsl:choose>

            <!-- Generate a course search form -->
            <xsl:when test="current() = 'lecturessearch'">

                <!-- Check if a pre-selected institute is set -->
                <xsl:variable name="preselect" select="../field[@index = 'preselectedInstitute']"/>
                <!-- Value "preselectedInstitute" set, so use it -->
                <xsl:if test="normalize-space(string($preselect/value/text())) != ''">
                    <field index="settings.preselectinst">
                        <value index="vDEF"><xsl:value-of select="normalize-space(string($preselect/value/text()))"/></value>
                    </field>
                </xsl:if>

            </xsl:when>

            <!-- Generate a link instead of directly showing content? -->
            <xsl:when test="current() = 'link'">
                <field index="settings.makelink">
                    <value index="vDEF">1</value>
                </field>

                <!-- Check if target format for link is set -->
                <xsl:variable name="linkformat" select="../field[@index = 'linkFormat']"/>
                <!-- Value "linkFormat" set, so use it -->
                <xsl:if test="normalize-space(string($linkformat/value/text())) != ''">
                    <field index="settings.linkformat">
                        <value index="vDEF"><xsl:value-of select="normalize-space(string($linkformat/value/text()))"/></value>
                    </field>
                </xsl:if>

                <!-- Check if link should point to another page -->
                <xsl:variable name="linktarget" select="../field[@index = 'linkTarget']"/>
                <!-- Value "linkTarget" set, so use it -->
                <xsl:if test="normalize-space(string($linktarget/value/text())) != ''">
                    <field index="settings.linktarget">
                        <value index="vDEF"><xsl:value-of select="normalize-space(string($linktarget/value/text()))"/></value>
                    </field>
                </xsl:if>

                <xsl:apply-templates select="field[@index = 'configs']"/>

            </xsl:when>

            <xsl:otherwise>
                <xsl:apply-templates select="field[@index = 'configs']"/>
            </xsl:otherwise>

        </xsl:choose>
    </xsl:template>

    <!--
        The field with index "configs" comes first, as the resulting pagetype
        specifies what other elements to consider
    -->
    <xsl:template match="field[@index = 'configs']">
        <!-- Extract selected module name -->
        <xsl:variable name="module" select="normalize-space(substring-before(current(), ';'))"/>
        <!-- Extract selected config_id -->
        <xsl:variable name="externconfig" select="normalize-space(substring-after(current(), ';'))"/>

        <!--
            Switch between different module names and generate
            corresponding page type.
        -->
        <xsl:variable name="pagetype">
            <xsl:choose>
                <!-- Course lists in different flavors -->
                <xsl:when test="$module='Lectures'">courses</xsl:when>
                <xsl:when test="$module='LecturesTable'">courses</xsl:when>
                <xsl:when test="$module='TemplateLectures'">courses</xsl:when>
                <xsl:when test="$module='TemplateSemBrowse'">courses</xsl:when>

                <!-- Person lists in different flavors -->
                <xsl:when test="$module='Lecturedetails'">coursedetails</xsl:when>
                <xsl:when test="$module='TemplateLecturedetails'">coursedetails</xsl:when>

                <!-- Person lists in different flavors -->
                <xsl:when test="$module='Persons'">persons</xsl:when>
                <xsl:when test="$module='TemplatePersons'">persons</xsl:when>
                <xsl:when test="$module='TemplatePersBrowse'">persons</xsl:when>

                <!-- Persondetails -->
                <xsl:when test="$module='Persondetails'">persondetails</xsl:when>
                <xsl:when test="$module='TemplatePersondetails'">persondetails</xsl:when>

                <!-- News -->
                <xsl:when test="$module='News'">news</xsl:when>
                <xsl:when test="$module='Newsticker'">news</xsl:when>
                <xsl:when test="$module='TemplateNews'">news</xsl:when>

                <!-- Download -->
                <xsl:when test="$module='Download'">download</xsl:when>
                <xsl:when test="$module='TemplateDownload'">download</xsl:when>

                <!-- No known module name, do nothing -->
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <!-- Create necessary elments if a valid pagetype was found. -->
        <xsl:if test="$pagetype != ''">
            <field index="settings.pagetype">
                <value index="vDEF"><xsl:value-of select="$pagetype"/></value>
            </field>
            <field index="settings.externconfig">
                <value index="vDEF"><xsl:value-of select="$externconfig"/></value>
            </field>
            <field index="settings.module">
                <value index="vDEF"><xsl:value-of select="$module"/></value>
            </field>
        </xsl:if>

        <!-- Now parse other fields. -->
        <xsl:apply-templates select="../field[@index!='configs']">
            <xsl:with-param name="pagetype" select="$pagetype"/>
        </xsl:apply-templates>
    </xsl:template>

    <!-- Process all "field" elements and create corresponding structure. -->
    <xsl:template match="field[@index != 'configs']">

        <xsl:choose>
            <!-- Course lists -->
            <xsl:when test="$pagetype = 'courses'">
                <xsl:choose>

                    <!-- Selected institute -->
                    <xsl:when test="current()/@index = 'institutes'">
                        <field index="settings.institute">
                            <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                        </field>
                    </xsl:when>

                    <!-- Selected course type -->
                    <xsl:when test="current()/@index = 'courseTypes'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <field index="settings.coursetype">
                                <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                            </field>
                        </xsl:if>
                    </xsl:when>

                    <!--
                        Show only selected subject area (and children).
                        This setting can be extracted from two different locations:
                        - mainSubjectAreas: First and second tree level
                        - subjectAreas: children of a selected mainSubjectArea
                        mainSubjectAreas need only be considered and migrated if no
                        subsequent subjectArea is selected.
                    -->
                    <xsl:when test="current()/@index = 'mainSubjectAreas'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <field index="settings.subject">
                            <xsl:choose>
                                <!-- Get value from "subjectAreas" field -->
                                <xsl:variable name="subjectareas" select="../field[@index = 'subjectAreas']"/>
                                <!-- Value "subjectAreas" set, so use it -->
                                <xsl:if test="normalize-space(string($subjectareas/value/text())) != ''">
                                    <value index="vDEF"><xsl:value-of select="normalize-space(string($subjectareas/value/text()))"/></value>
                                </xsl:if>
                                <!-- No value "subjectAreas" set, use "mainSubjectAreas" -->
                                <xsl:otherwise>
                                    <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                                </xsl:otherwise>
                            </xsl:choose>
                            </field>
                        </xsl:if>
                    </xsl:when>

                    <!-- Show courses only at home or at participating institutes, too? -->
                    <xsl:when test="current()/@index = 'allInstitutes'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <xsl:if test="normalize-space(string(current())) != '0'">
                                <field index="settings.participating">
                                    <value index="vDEF">1</value>
                                </field>
                            </xsl:if>
                        </xsl:if>
                    </xsl:when>

                    <!-- Aggregation (collect data from sub institutes)? -->
                    <xsl:when test="current()/@index = 'aggregate'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <xsl:if test="normalize-space(string(current())) != '0'">
                                <field index="settings.aggregate">
                                    <value index="vDEF">1</value>
                                </field>
                            </xsl:if>
                        </xsl:if>
                    </xsl:when>

                </xsl:choose>
            </xsl:when>
            <xsl:when test="$pagetype = 'coursedetails'">
                <xsl:choose>

                    <!-- Selected course -->
                    <xsl:when test="current()/@index = 'lectures'">
                        <field index="settings.coursesearch">
                            <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                        </field>
                    </xsl:when>

                    <!-- Selected institute -->
                    <xsl:when test="current()/@index = 'institutes'">
                        <field index="settings.choose_course_institute">
                            <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                        </field>
                    </xsl:when>

                </xsl:choose>
            </xsl:when>
            <xsl:when test="$pagetype = 'persons'">
                <xsl:choose>

                    <!-- Selected institute -->
                    <xsl:when test="current()/@index = 'institutes'">
                        <field index="settings.institute">
                            <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                        </field>
                    </xsl:when>

                    <!-- Selected statusgroup -->
                    <xsl:when test="current()/@index = 'groups'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <field index="settings.statusgroup">
                                <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                            </field>
                        </xsl:if>
                    </xsl:when>

                </xsl:choose>
            </xsl:when>
            <xsl:when test="$pagetype = 'persondetails'">

                <!-- Selected course -->
                <xsl:when test="current()/@index = 'contacts'">
                    <field index="settings.personsearch">
                        <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                    </field>
                </xsl:when>

                <!-- Selected institute -->
                <xsl:when test="current()/@index = 'institutes'">
                    <field index="settings.choose_user_institute">
                        <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                    </field>
                </xsl:when>

            </xsl:when>
            <xsl:when test="$pagetype = 'news'">

                <!-- Small news display -->
                <xsl:if test="current()/@index = 'newsOneCol'">
                    <xsl:if test="normalize-space(string(current())) != ''">
                        <xsl:if test="normalize-space(string(current())) != '0'">
                            <field index="settings.smallnews">
                                <value index="vDEF">1</value>
                            </field>
                        </xsl:if>
                    </xsl:if>
                </xsl:if>

            </xsl:when>
            <xsl:when test="$pagetype = 'download'">

            </xsl:when>
        </xsl:choose>

    </xsl:template>

</xsl:stylesheet>
