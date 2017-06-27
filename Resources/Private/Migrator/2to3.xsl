<?xml version="1.0" encoding="ISO-8859-1"?>

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/TR/REC-html40">

    <xsl:output method="xml" version="1.0" encoding="utf-8" standalone="yes" indent="yes"/>

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
            <sheet index="sDEF">
                <xsl:apply-templates select="language"/>
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
            <xsl:when test="normalize-space(string(current())) = 'lecturessearch'">

                <!-- Set search as target page type. -->
                <field index="settings.pagetype">
                    <value index="vDEF">searchpage</value>
                </field>

                <!-- Check if a pre-selected institute is set -->
                <xsl:variable name="preselect" select="../field[@index = 'preselectedInstitute']"/>
                <!-- Value "preselectedInstitute" set, so use it -->
                <xsl:if test="normalize-space(string($preselect/value/text())) != ''">
                    <field index="settings.preselectinst">
                        <value index="vDEF"><xsl:value-of select="normalize-space(string($preselect/value/text()))"/></value>
                    </field>
                </xsl:if>

                <xsl:apply-templates select="../field[@index = 'configs']"/>

            </xsl:when>

            <!-- Generate a link instead of directly showing content? -->
            <xsl:when test="normalize-space(string(current())) = 'link'">
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

                <!-- Check if target text for link is set -->
                <xsl:variable name="linktext" select="../field[@index = 'linkText']"/>
                <!-- Set link text -->
                <xsl:if test="normalize-space(string($linktext/value/text())) != ''">
                    <field index="settings.linktext">
                        <value index="vDEF"><xsl:value-of select="normalize-space(string($linktext/value/text()))"/></value>
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

                <xsl:apply-templates select="../field[@index = 'configs']"/>

            </xsl:when>

            <xsl:otherwise>
                <xsl:apply-templates select="../field[@index = 'configs']"/>
            </xsl:otherwise>

        </xsl:choose>

    </xsl:template>

    <!--
        The field with index "configs" comes first, as the resulting pagetype
        specifies what other elements to consider
    -->
    <xsl:template match="field[@index = 'configs']">
        <!-- Extract selected module name -->
        <xsl:variable name="module" select="substring-before(normalize-space(string(current())), ';')"/>
        <!-- Extract selected config_id -->
        <xsl:variable name="externconfig" select="substring-after(normalize-space(string(current())), ';')"/>

        <!--
            Switch between different module names and generate
            corresponding page type.
        -->
        <xsl:variable name="pagetype">
            <xsl:choose>
                <!-- Course lists in different flavors -->
                <xsl:when test="$module='Lectures' or $module='LecturesTable' or $module='TemplateLectures' or $module='TemplateSemBrowse'">courses</xsl:when>

                <!-- Person lists in different flavors -->
                <xsl:when test="$module='Lecturedetails' or $module='TemplateLecturedetails'">coursedetails</xsl:when>

                <!-- Person lists in different flavors -->
                <xsl:when test="$module='Persons' or $module='TemplatePersons' or $module='TemplatePersBrowse'">persons</xsl:when>

                <!-- Persondetails -->
                <xsl:when test="$module='Persondetails' or $module='TemplatePersondetails'">persondetails</xsl:when>

                <!-- News -->
                <xsl:when test="$module='News' or $module='Newsticker' or $module='TemplateNews'">news</xsl:when>

                <!-- Download -->
                <xsl:when test="$module='Download' or $module='TemplateDownload'">download</xsl:when>

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
        <xsl:apply-templates select="../field[@index != 'contentType' and @index != 'configs']">
            <xsl:with-param name="ptype" select="$pagetype"/>
            <xsl:with-param name="modulename" select="$module"/>
        </xsl:apply-templates>
    </xsl:template>

    <!-- Process all "field" elements and create corresponding structure. -->
    <xsl:template match="field[@index != 'contentType' and @index != 'configs']">

        <xsl:param name="ptype"/>
        <xsl:param name="modulename"/>

        <xsl:choose>
            <!-- Course lists -->
            <xsl:when test="$ptype = 'courses'">
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
                        <xsl:choose>
                            <xsl:when test="../field[@index = 'subjectAreas']">
                                <xsl:if test="normalize-space(../field[@index = 'subjectAreas']/value/text()) != ''">
                                    <!-- Value "subjectAreas" set, so use it -->
                                    <field index="settings.subject">
                                        <value index="vDEF"><xsl:value-of select="normalize-space(../field[@index = 'subjectAreas']/value/text())"/></value>
                                    </field>
                                </xsl:if>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:if test="normalize-space(current()) != ''">
                                    <field index="settings.subject">
                                        <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                                    </field>
                                </xsl:if>
                            </xsl:otherwise>
                        </xsl:choose>
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

                    <!-- Should links to course details point to another page? -->
                    <xsl:when test="current()/@index = 'lectureDetailTarget'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <xsl:if test="normalize-space(string(current())) != '0'">
                                <field index="settings.coursedetailtarget">
                                    <value index="vDEF"><xsl:value-of select="normalize-space(string(current()))"/></value>
                                </field>
                            </xsl:if>
                        </xsl:if>
                    </xsl:when>

                    <!-- Should links to person details point to another page? -->
                    <xsl:when test="current()/@index = 'personDetailTarget'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <xsl:if test="normalize-space(string(current())) != '0'">
                                <field index="settings.persondetailtarget">
                                    <value index="vDEF"><xsl:value-of select="normalize-space(string(current()))"/></value>
                                </field>
                            </xsl:if>
                        </xsl:if>
                    </xsl:when>

                </xsl:choose>
            </xsl:when>
            <xsl:when test="$ptype = 'coursedetails'">
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

                    <!-- Should links to person details point to another page? -->
                    <xsl:when test="current()/@index = 'personDetailTarget'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <xsl:if test="normalize-space(string(current())) != '0'">
                                <field index="settings.persondetailtarget">
                                    <value index="vDEF"><xsl:value-of select="normalize-space(string(current()))"/></value>
                                </field>
                            </xsl:if>
                        </xsl:if>
                    </xsl:when>

                </xsl:choose>
            </xsl:when>
            <xsl:when test="$ptype = 'persons'">
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

                    <!-- Should links to person details point to another page? -->
                    <xsl:when test="current()/@index = 'personDetailTarget'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <xsl:if test="normalize-space(string(current())) != '0'">
                                <field index="settings.persondetailtarget">
                                    <value index="vDEF"><xsl:value-of select="normalize-space(string(current()))"/></value>
                                </field>
                            </xsl:if>
                        </xsl:if>
                    </xsl:when>

                </xsl:choose>
            </xsl:when>
            <xsl:when test="$ptype = 'persondetails'">

                <xsl:choose>
                    <!-- Selected person -->
                    <xsl:when test="current()/@index = 'persons' and $modulename = 'Persondetails'">
                        <field index="settings.personsearch">
                            <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                        </field>
                    </xsl:when>

                    <!-- Selected person part 2 -->
                    <xsl:when test="current()/@index = 'contacts' and $modulename = 'TemplatePersondetails'">
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

                    <!-- Should links to course details point to another page? -->
                    <xsl:when test="current()/@index = 'lectureDetailTarget'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <xsl:if test="normalize-space(string(current())) != '0'">
                                <field index="settings.coursedetailtarget">
                                    <value index="vDEF"><xsl:value-of select="normalize-space(string(current()))"/></value>
                                </field>
                            </xsl:if>
                        </xsl:if>
                    </xsl:when>

                </xsl:choose>

            </xsl:when>
            <xsl:when test="$ptype = 'news'">

                <xsl:choose>
                    <!-- Selected institute -->
                    <xsl:when test="current()/@index = 'institutes'">
                        <field index="settings.institute">
                            <value index="vDEF"><xsl:value-of select="normalize-space(current())"/></value>
                        </field>
                    </xsl:when>

                    <!-- Small news display -->
                    <xsl:when test="current()/@index = 'newsOneCol'">
                        <xsl:if test="normalize-space(string(current())) != '' and normalize-space(string(current())) != '0'">
                            <field index="settings.smallnews">
                                <value index="vDEF">1</value>
                            </field>
                        </xsl:if>
                    </xsl:when>

                    <!-- Should links to news details point to another page? -->
                    <xsl:when test="current()/@index = 'newsDetailTarget'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <xsl:if test="normalize-space(string(current())) != '0'">
                                <field index="settings.newsdetailtarget">
                                    <value index="vDEF"><xsl:value-of select="normalize-space(string(current()))"/></value>
                                </field>
                            </xsl:if>
                        </xsl:if>
                    </xsl:when>

                    <!-- Should links to person details point to another page? -->
                    <xsl:when test="current()/@index = 'personDetailTarget'">
                        <xsl:if test="normalize-space(string(current())) != ''">
                            <xsl:if test="normalize-space(string(current())) != '0'">
                                <field index="settings.persondetailtarget">
                                    <value index="vDEF"><xsl:value-of select="normalize-space(string(current()))"/></value>
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
            <xsl:when test="$ptype = 'download'">

            </xsl:when>
        </xsl:choose>

    </xsl:template>

</xsl:stylesheet>
