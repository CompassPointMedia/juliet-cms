<h3>Products Component</h3>

<a href="#documentation">documentation</a>&nbsp;&nbsp;<a href="#changelog">changelog</a>&nbsp;&nbsp;<a href="#todo">todo list</a><br />
<br />

<a name="documentation"></a>
<h3>Documentation</h3>
<p>2012-12-20 - starting notes on how products works</p>
<p>We are studying the page named /options-pricing-playsets - page node is 17, and the data in gen_ and etc. is:</p>
<p>gen_nodes:<br />
ID=17<br />
Type=Object - useful for structure queries<br />
  PageType=products:list - this equates to the &quot;Page type&quot; field in console/rsc_pagemanager_focus.php<br />
ComponentLocation={php code} - <span class="gray">I believe I specified somewhere that ComponentLocation is a poor name for this field - all it has ever been has been a PHP code repository.</span></p>
<p>gen_<strong>nodes</strong>_settings:<br />
  Nodes_ID=17<br /> 
Settings=array; THIS IS WHERE SETTINGS FOR products.php ARE STORED ON THIS PAGE. (this is a base64 string, and also used for bottom options on 
console/rsc_pagemanager_focus.php)<br />
NOTE: other components like nav store settings in gen_templates_<strong>blocks</strong>.Parameters - this is when they are &quot;cross-page, single block&quot; - see /components-juliet/.sample.componentfile.php for examples of both methods </p>
<p>pJ_getdata() - this function retrieves variables for the page from $pJ['componentFiles'] - but this is set by the products.php componentfile itself <br />
the only other current (2012-12-20) reference to $pJ['componentFiles'] is in function pJVarParser() - which makes sense so that CMS regions can reference vars set by multiple components.</p>
<p>&nbsp; </p>
<br />
<br />
<a name="changelog"></a>
<h3>Changelog</h3>
<p>2012-12-21: implemented prodCustomSectionEval. This means for list view or focus view I can custom-arrange the order of output such as ... prodSKU, prodName, somethingElse2, prodDescription, somethingElse1 prodAdd, prodPrice.. <br />
where somethingElse1 and somethingElse2 are defined as nodes e.g. $prodCustomSections['somethingElse1'] etc.<br />
<strong>NOTE</strong>: <span class="red">prodCustomSectionEval is processed in products.focus.php and products.list.php, just after $rdp is declared and before settings and ecom_flex are declared. prodCustomSectionEval is eval'd() each time. Make sure you do not have any requires() that will cause a crash if required twice, or have any loop variables which will conflict if in list mode.</span> <br />
NOTE: I'm still lacking in the ability to subnest divs in each other but see docs in products.php for this date, for some prelim. notes on how to approach this. <br />
  <br />
  <br />
  <a name="todo"></a></p>
<h3>Todo</h3>
<br />
<br />
<br />
<br />
