<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<style type="text/css">
<!--
.style3 {color: #990000; font-weight: bold; }
-->
</style>
</head>

<body>
<h1>Importing Data </h1>
<p>RelateBase accounting allows you to import invoices and transactions from a Comma-Separated Value (CSV) format. The approach we have taken at RelateBase is to allow imports to be as flexible as possible so that you can get your data in and begin using our system. The following tutorial explains imports into the accounting system starting from the simplest format and then building in complexity. Let us know what you think and how we can improve this tutorial! </p>
<p>The minimum number of fields for a import are: Type, Number, Name and Amount. Fields must be labeled on the first row, and are case-insensitive. So, the following import would create two invoices just fine:</p>
<table cellpadding="0">
  <tr>
    <th scope="col">Type</th>
    <th scope="col">Number</th>
    <th scope="col">Name</th>
    <th scope="col">Amount</th>
  </tr>
  <tr>
    <td>Inv</td>
    <td>4012</td>
    <td>Smith, John </td>
    <td>1496.25</td>
  </tr>
  <tr>
    <td>Inv</td>
    <td>4013</td>
    <td>Johnson, Millie </td>
    <td>375.00</td>
  </tr>
</table>
<p>Note the Type field. Normally it is to the left but does not have to be. RelateBase is designed to be flexible and forgiving. Recognized values for the Type field are found below. The above import would work but it would assume many things for you:</p>
<ul>
  <li>  The invoice would be part of an asset account called &quot;Accounts Receivable&quot; and would be created if not present</li>
  <li>The item used would be called &quot;General Sales&quot; and it would be a non-inventory part (it would be created if not present) </li>
  <li>The account for the item would be an Asset account  called &quot;General Income&quot;     (it would be created if not present)</li>
  <li>The date of the invoice would be the date of import</li>
  <li>The customer would be created with no additional information, if not present. RelateBase would match Smith, John and John Smith</li>
</ul>
<p>That addresses the simplest import; you probably will not use that format (nor should you) but it is helpful to know where to start</p>
<p>Next we need to know how to handle  &quot;splits&quot;, or multiple line items on an invoice. The rule is this: all lines with the same Number field are treated as part of the same transaction. So let's break the above invoices down:<br />
</p>
<table cellpadding="0">
  <tr>
    <th scope="col">Type</th>
    <th scope="col">Number</th>
    <th scope="col">Name</th>
    <th scope="col">Amount</th>
  </tr>
  <tr>
    <td>Inv</td>
    <td>4012</td>
    <td>Smith, John </td>
    <td>546.25</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>4012</td>
    <td>&nbsp;</td>
    <td>625.00</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>4012</td>
    <td>&nbsp;</td>
    <td>325.00</td>
  </tr>
  <tr>
    <td>Inv</td>
    <td>4013</td>
    <td>Johnson, Millie </td>
    <td>175.00</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>4013</td>
    <td>&nbsp;</td>
    <td>200.00</td>
  </tr>
</table>
<p>You may notice some fields are blank, including the Type field. Until either Type or Number fields change, RelateBase assumes you are in the same transaction. You may have values for Type and Customer if you wish.</p>
<p>Next we need to address specifics of Account and Item. This is simple:</p>
<table cellpadding="0">
  <tr>
    <th scope="col">Type</th>
    <th scope="col">Number</th>
    <th scope="col">Name</th>
    <th scope="col">Parent Account </th>
    <th scope="col">Account</th>
    <th scope="col">Item</th>
    <th scope="col">Amount</th>
  </tr>
  <tr>
    <td>Inv</td>
    <td>4012</td>
    <td>Smith, John </td>
    <td>Accounts Receivable </td>
    <td>Lumber </td>
    <td>2x12x16' Douglas Fir </td>
    <td>546.25</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>4012</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>Hardware</td>
    <td>Moen Faucet Set FS-123 </td>
    <td>625.00</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>4012</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>Hardware</td>
    <td>PP-309 Plumber's Putty </td>
    <td>325.00</td>
  </tr>
  <tr>
    <td>Inv</td>
    <td>4013</td>
    <td>Johnson, Millie </td>
    <td>Accounts Receivable </td>
    <td>Services</td>
    <td>Window Re-Screening </td>
    <td>175.00</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>4013</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>Housewares</td>
    <td>Patio Table/Chair Set </td>
    <td>200.00</td>
  </tr>
</table>
<p>Note one important thing: the field named Parent Account. This is different from Intuit's method of having a separate row for the parent account. Again, if Parent Account were not present, then Accounts Receivable would be assumed when the Type is Inv. Note: all RelateBase fields are case-insensitive and also space-insensitive, so Parent Account or ParentAccount both work. </p>
<p>Note three important points here:</p>
<ul>
  <li>If only Account is specified, then an item called &quot;General Sales&quot; will either be created or used, with that account.</li>
  <li>If only Item is specified, then an Account called &quot;General Income&quot; will either be created or used, with that item</li>
  <li>The Account and Item fields can be totally out of synch with what you have in your database. This would not be typical, but there are times where items get re-assigned to a different income or expense structure.</li>
</ul>
<p>Now let's address Quantity and Rate. RelateBase has three fields that are used to calculate line items: Quantity, Price(or Rate), and Amount. Only Amount is required. If Quantity and Amount are included, then Price will be LEFT BLANK. Similarly, f Price and Amount are included, then Quantity will left blank. If all three fields are present, then it must be true that ROUND(Quantity * Price, 2) = ROUND(Amount, 2), or an error will result and that transaction will not be imported. Amount is the &quot;final authority&quot; on the line item value and is rounded to two decimal places (cents). Here is our import with these fields added:</p>
<table cellpadding="0">
  <tr>
    <th scope="col">Type</th>
    <th scope="col">Number</th>
    <th scope="col">Date</th>
    <th scope="col">Name</th>
    <th scope="col">Parent Account </th>
    <th scope="col">Account</th>
    <th scope="col">SKU</th>
    <th scope="col">Item</th>
    <th scope="col">Description</th>
    <th scope="col">Quantity</th>
    <th scope="col">Price</th>
    <th scope="col">&nbsp;</th>
    <th scope="col">Amount</th>
  </tr>
  <tr>
    <td>Inv</td>
    <td>4012</td>
    <td><p>2012-07-15</p>    </td>
    <td>Smith, John </td>
    <td>Accounts Receivable </td>
    <td>Lumber </td>
    <td>DF2x2x16</td>
    <td>2x12x16' Douglas Fir </td>
    <td>&nbsp;</td>
    <td>23</td>
    <td>23.75</td>
    <td>&nbsp;</td>
    <td>546.25</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>4012</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>Hardware</td>
    <td>FS-123</td>
    <td>Moen Faucet Set FS-123 </td>
    <td>discount; slightly scratched </td>
    <td>&nbsp;</td>
    <td>325.00</td>
    <td>&nbsp;</td>
    <td>625.00</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>4012</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>Hardware</td>
    <td>PP-309</td>
    <td>PP-309 Plumber's Putty </td>
    <td>&nbsp;</td>
    <td>65</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>325.00</td>
  </tr>
  <tr>
    <td>Inv</td>
    <td>4013</td>
    <td><p>8/3/2012</p>    </td>
    <td>Johnson, Millie </td>
    <td>Accounts Receivable </td>
    <td>Services</td>
    <td>WRS</td>
    <td>Window Re-Screening </td>
    <td>7 ea. 30&quot;x60&quot; </td>
    <td><span class="style3">8</span></td>
    <td><span class="style3">25.00</span></td>
    <td>&nbsp;</td>
    <td><span class="style3">175.00</span></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>4013</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>Housewares</td>
    <td>DOW8924</td>
    <td>Patio Table/Chair Set </td>
    <td>&nbsp;</td>
    <td>1</td>
    <td>200.00</td>
    <td>&nbsp;</td>
    <td>200.00</td>
  </tr>
</table>
<p>Note that line 4 contains an error; invoice #4013 would not be imported due to this error (the remaining examples correct this to Quantity=7). </p>
<p>Note also the Date has been included. A wide range of date formats will be recognized. Again, if a Date field is present, the date must be valid (or blank to equal today's date). We have also included the SKU (Also sometimes referred to as Part Number; SKU stands for Stock Keeping Unit). If the SKU column is not present, then the item name will be used as a reference to create a new item. If the SKU column IS present, then it will be used as the reference. The Description column will be used for that specific line item entry, however it will also be used as the item default description if the item must be created.</p>
<p>Importing Chart of Accounts, Items and Customers</p>
<p>These three parts of the database are more straightforward than transactions since there are no &quot;splits&quot; or multiple lines. The recommended order that you import data into RelateBase is as follows:</p>
<ul>
  <li>Chart of Accounts</li>
  <li>Items</li>
  <li>Customers</li>
  <li>Transactions</li>
</ul>
<p>This way, each additional section can build upon the previous section of data imported.</p>
<table cellpadding="0">
  <tr>
    <th scope="col">Type</th>
    <th scope="col">SKU</th>
    <th scope="col">Name</th>
    <th scope="col">Description</th>
    <th scope="col">Long Description </th>
    <th scope="col">Price</th>
    <th scope="col">Weight</th>
    <th scope="col">Account</th>
  </tr>
  <tr>
    <td>Item</td>
    <td>DF2x2x16</td>
    <td>2x12x16' Douglas Fir </td>
    <td>[HTML OK] </td>
    <td>[HTML OK] </td>
    <td>23.75</td>
    <td>&nbsp;</td>
    <td>Lumber </td>
  </tr>
  <tr>
    <td>Inventory Part </td>
    <td>FS-123</td>
    <td>Moen Faucet Set FS-123 </td>
    <td>A good starter faucet set with Moen's legendary quality and durability </td>
    <td>[HTML OK] </td>
    <td>325.00</td>
    <td>32.5</td>
    <td>Hardware</td>
  </tr>
  <tr>
    <td>Non Inventory Part </td>
    <td>PP-309</td>
    <td>PP-309 Plumber's Putty </td>
    <td>&nbsp;</td>
    <td>[HTML OK] </td>
    <td>5.00</td>
    <td>&nbsp;</td>
    <td>Hardware</td>
  </tr>
  <tr>
    <td>Service</td>
    <td>WRS</td>
    <td>Window Re-Screening </td>
    <td>&nbsp;</td>
    <td>[HTML OK] </td>
    <td><p>25.00</p>
    </td>
    <td>&nbsp;</td>
    <td>Services</td>
  </tr>
</table>
<p>The only REQUIRED fields to import an item are Type (if Type=Item, it will default to Non-Inventory Part), and either SKU or Name. SKU is considered the &quot;primary key&quot; of the item. If SKU is present in the products table already, then this will update that product, so be careful. If only the Name field is present, it will compare with the Name value for other items in the database.</p>
<h3>Chart of Accounts </h3>
<table cellpadding="0">
  <tr>
    <th scope="col">Type</th>
    <th scope="col">Name</th>
    <th scope="col">Number</th>
  </tr>
  <tr>
    <td>Expense</td>
    <td>Professional Fees:Dues and Subscriptions </td>
    <td>2011</td>
  </tr>
  <tr>
    <td>Asset</td>
    <td>Loan to Officer </td>
    <td>5314</td>
  </tr>
  <tr>
    <td>Accounts Receivable </td>
    <td>Accounts Receivable </td>
    <td>2001</td>
  </tr>
  <tr>
    <td>Income</td>
    <td>Lumber Sales </td>
    <td>3804</td>
  </tr>
</table>
<p>The same system of a separating sub-accounts with a colon, as used by Intuit, is also recognized by RelateBase.</p>
<h3>Multiple Data Types </h3>
<p>You can import multiple  types of data in a single file. Just ensure that the proper values go in the proper columns. You can, in theory, have a column for all types of data in your import, and simply make sure required fields are filled in for each. However, it is more practical to simply insert a new header row specific to the next data type. RelateBase will recognize any row as a header row when a) It has the key field <u>Type</u> present and b) all columns are recognized field names.</p>
<h3>Custom Attributes</h3>
<p>If you have custom fields set up in your database for different types of data, you can import data into these fields by prefixing the phrase Custom Attribute, as in:</p>
<p>Custom Attribute:Color Chart</p>
<p>Again, the field is case-insensitive and space insensitive. The colon is optional </p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp; </p>
<p>&nbsp; </p>
<p>&nbsp;  </p>
<p>&nbsp; </p>
<p>&nbsp; </p>
<p>&nbsp; </p>
<p>&nbsp; </p>
<p>&nbsp; </p>
</body>
</html>
