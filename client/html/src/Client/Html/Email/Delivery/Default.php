<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2014
 * @license LGPLv3, http://www.arcavias.com/en/license
 * @package Client
 * @subpackage Html
 */


/**
 * Default implementation of delivery emails.
 *
 * @package Client
 * @subpackage Html
 */
class Client_Html_Email_Delivery_Default
	extends Client_Html_Abstract
{
	/** client/html/email/delivery/default/subparts
	 * List of HTML sub-clients rendered within the email delivery section
	 *
	 * The output of the frontend is composed of the code generated by the HTML
	 * clients. Each HTML client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain HTML clients themselves and therefore a
	 * hierarchical tree of HTML clients is composed. Each HTML client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the HTML code generated by the parent is printed, then
	 * the HTML code of its sub-clients. The order of the HTML sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural HTML, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2014.03
	 * @category Developer
	 */
	private $_subPartPath = 'client/html/email/delivery/default/subparts';

	/** client/html/email/delivery/text/name
	 * Name of the text part used by the email delivery client implementation
	 *
	 * Use "Myname" if your class is named "Client_Html_Email_Delivery_Text_Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.03
	 * @category Developer
	 */

	/** client/html/email/delivery/html/name
	 * Name of the html part used by the email delivery client implementation
	 *
	 * Use "Myname" if your class is named "Client_Html_Email_Delivery_Html_Myname".
	 * The name is case-sensitive and you should avoid camel case names like "MyName".
	 *
	 * @param string Last part of the client class name
	 * @since 2014.03
	 * @category Developer
	 */
	private $_subPartNames = array( 'text', 'html' );


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @return string HTML code
	 */
	public function getBody()
	{
		$view = $this->_setViewParams( $this->getView() );

		$content = '';
		foreach( $this->_getSubClients( $this->_subPartPath, $this->_subPartNames ) as $subclient ) {
			$content .= $subclient->setView( $view )->getBody();
		}
		$view->deliveryBody = $content;

		/** client/html/email/delivery/default/template-body
		 * Relative path to the text body template of the email delivery client.
		 *
		 * The template file contains the text and processing instructions
		 * to generate the result shown in the e-mail. The
		 * configuration string is the path to the template file relative
		 * to the layouts directory (usually in client/html/layouts).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "default" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "default"
		 * should be replaced by the name of the new class.
		 *
		 * The email delivery text client allows to use a different template for
		 * each delivery status value. You can create a template for each delivery
		 * status and store it in the "email/delivery/<status number>/" directory
		 * below the "layouts" directory (usually in client/html/layouts). If no
		 * specific layout template is found, the common template in the
		 * "email/delivery/" directory is used.
		 *
		 * @param string Relative path to the template creating code for the e-mail body
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/email/delivery/default/template-header
		 */
		$tplconf = 'client/html/email/delivery/default/template-body';

		$status = $view->extOrderItem->getDeliveryStatus();
		$default = array( 'email/delivery/' . $status . '/body-default.html', 'email/delivery/body-default.html' );

		return $view->render( $this->_getTemplate( $tplconf, $default ) );
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @return string String including HTML tags for the header
	 */
	public function getHeader()
	{
		$view = $this->_setViewParams( $this->getView() );

		$content = '';
		foreach( $this->_getSubClients( $this->_subPartPath, $this->_subPartNames ) as $subclient ) {
			$content .= $subclient->setView( $view )->getHeader();
		}
		$view->deliveryHeader = $content;


		$addr = $view->extOrderBaseItem->getAddress( MShop_Order_Item_Base_Address_Abstract::TYPE_PAYMENT );

		$msg = $view->mail();
		$msg->addHeader( 'X-MailGenerator', 'Arcavias' );
		$msg->addTo( $addr->getEMail(), $addr->getFirstName() . ' ' . $addr->getLastName() );


		/** client/html/email/from-name
		 * Name used when sending e-mails
		 *
		 * The name of the person or e-mail account that is used for sending all
		 * shop related emails to customers.
		 *
		 * @param string Name shown in the e-mail
		 * @since 2014.03
		 * @category User
		 * @see client/html/email/delivery/from-name
		 * @see client/html/email/from-email
		 * @see client/html/email/reply-email
		 * @see client/html/email/bcc-email
		 */
		$fromName = $view->config( 'client/html/email/from-name' );

		/** client/html/email/delivery/from-name
		 * Name used when sending delivery e-mails
		 *
		 * The name of the person or e-mail account that is used for sending all
		 * shop related delivery e-mails to customers. This configuration option
		 * overwrites the name set in "client/html/email/from-name".
		 *
		 * @param string Name shown in the e-mail
		 * @since 2014.03
		 * @category User
		 * @see client/html/email/from-name
		 * @see client/html/email/from-email
		 * @see client/html/email/reply-email
		 * @see client/html/email/bcc-email
		 */
		$fromNameDelivery = $view->config( 'client/html/email/delivery/from-name', $fromName );

		/** client/html/email/from-email
		 * E-Mail address used when sending e-mails
		 *
		 * The e-mail address of the person or account that is used for sending
		 * all shop related emails to customers.
		 *
		 * @param string E-mail address
		 * @since 2014.03
		 * @category User
		 * @see client/html/email/from-name
		 * @see client/html/email/delivery/from-email
		 * @see client/html/email/reply-email
		 * @see client/html/email/bcc-email
		 */
		$fromEmail = $view->config( 'client/html/email/from-email' );

		/** client/html/email/delivery/from-email
		 * E-Mail address used when sending delivery e-mails
		 *
		 * The e-mail address of the person or account that is used for sending
		 * all shop related delivery emails to customers. This configuration option
		 * overwrites the e-mail address set via "client/html/email/from-email".
		 *
		 * @param string E-mail address
		 * @since 2014.03
		 * @category User
		 * @see client/html/email/delivery/from-name
		 * @see client/html/email/from-email
		 * @see client/html/email/reply-email
		 * @see client/html/email/bcc-email
		 */
		if( ( $fromEmailDelivery = $view->config( 'client/html/email/delivery/from-email', $fromEmail ) ) != null ) {
			$msg->addFrom( $fromEmailDelivery, $fromNameDelivery );
		}


		/** client/html/email/reply-name
		 * Recipient name displayed when the customer replies to e-mails
		 *
		 * The name of the person or e-mail account the customer should
		 * reply to in case of questions or problems. If no reply name is
		 * configured, the name person or e-mail account set via
		 * "client/html/email/from-name" is used.
		 *
		 * @param string Name shown in the e-mail
		 * @since 2014.03
		 * @category User
		 * @see client/html/email/reply-email
		 * @see client/html/email/delivery/reply-email
		 * @see client/html/email/from-email
		 * @see client/html/email/from-name
		 * @see client/html/email/bcc-email
		 */
		$replyName = $view->config( 'client/html/email/reply-name', $fromName );

		/** client/html/email/delivery/reply-name
		 * Recipient name displayed when the customer replies to delivery e-mails
		 *
		 * The name of the person or e-mail account the customer should
		 * reply to in case of questions or problems. This configuration option
		 * overwrites the name set via "client/html/email/reply-name".
		 *
		 * @param string Name shown in the e-mail
		 * @since 2014.03
		 * @category User
		 * @see client/html/email/delivery/reply-email
		 * @see client/html/email/reply-name
		 * @see client/html/email/reply-email
		 * @see client/html/email/from-email
		 * @see client/html/email/bcc-email
		 */
		$replyNameDelivery = $view->config( 'client/html/email/delivery/reply-name', $replyName );

		/** client/html/email/reply-email
		 * E-Mail address used by the customer when replying to e-mails
		 *
		 * The e-mail address of the person or e-mail account the customer
		 * should reply to in case of questions or problems.
		 *
		 * @param string E-mail address
		 * @since 2014.03
		 * @category User
		 * @see client/html/email/reply-name
		 * @see client/html/email/delivery/reply-email
		 * @see client/html/email/from-email
		 * @see client/html/email/bcc-email
		 */
		$replyEmail = $view->config( 'client/html/email/reply-email', $fromEmail );

		/** client/html/email/delivery/reply-email
		 * E-Mail address used by the customer when replying to delivery e-mails
		 *
		 * The e-mail address of the person or e-mail account the customer
		 * should reply to in case of questions or problems. This configuration
		 * option overwrites the e-mail address set via "client/html/email/reply-email".
		 *
		 * @param string E-mail address
		 * @since 2014.03
		 * @category User
		 * @see client/html/email/delivery/reply-name
		 * @see client/html/email/reply-email
		 * @see client/html/email/from-email
		 * @see client/html/email/bcc-email
		 */
		if( ( $replyEmailDelivery = $view->config( 'client/html/email/delivery/reply-email', $replyEmail ) ) != null ) {
			$msg->addReplyTo( $replyEmailDelivery, $replyNameDelivery );
		}


		/** client/html/email/bcc-email
		 * E-Mail address all e-mails should be also sent to
		 *
		 * Using this option you can send a copy of all shop related e-mails to
		 * a second e-mail account. This can be handy for testing and checking
		 * the e-mails sent to customers.
		 *
		 * It also allows shop owners with a very small volume of orders to be
		 * notified about new orders. Be aware that this isn't useful if the
		 * order volumne is high or has peeks!
		 *
		 * @param string E-mail address
		 * @since 2014.03
		 * @category User
		 * @category Developer
		 * @see client/html/email/delivery/bcc-email
		 * @see client/html/email/reply-email
		 * @see client/html/email/from-email
		 */
		$bccEmail = $view->config( 'client/html/email/bcc-email' );

		/** client/html/email/delivery/bcc-email
		 * E-Mail address all delivery e-mails should be also sent to
		 *
		 * Using this option you can send a copy of all delivery related e-mails
		 * to a second e-mail account. This can be handy for testing and checking
		 * the e-mails sent to customers.
		 *
		 * It also allows shop owners with a very small volume of orders to be
		 * notified about new orders. Be aware that this isn't useful if the
		 * order volumne is high or has peeks!
		 *
		 * This configuration option overwrites the e-mail address set via
		 * "client/html/email/bcc-email".
		 *
		 * @param string E-mail address
		 * @since 2014.03
		 * @category User
		 * @category Developer
		 * @see client/html/email/bcc-email
		 * @see client/html/email/reply-email
		 * @see client/html/email/from-email
		 */
		if( ( $bccEmailDelivery = $view->config( 'client/html/email/delivery/bcc-email', $bccEmail ) ) != null ) {
			$msg->addBcc( $bccEmailDelivery );
		}


		/** client/html/email/delivery/default/template-header
		 * Relative path to the text header template of the email delivery client.
		 *
		 * The template file contains the text and processing instructions
		 * to generate the text that is inserted into the header
		 * of the e-mail. The configuration string is the
		 * path to the template file relative to the layouts directory (usually
		 * in client/html/layouts).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "default" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "default"
		 * should be replaced by the name of the new class.
		 *
		 * The email payment text client allows to use a different template for
		 * each payment status value. You can create a template for each payment
		 * status and store it in the "email/payment/<status number>/" directory
		 * below the "layouts" directory (usually in client/html/layouts). If no
		 * specific layout template is found, the common template in the
		 * "email/payment/" directory is used.
		 *
		 * @param string Relative path to the template creating code for the e-mail header
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/email/delivery/default/template-body
		 */
		$tplconf = 'client/html/email/delivery/default/template-header';

		$status = $view->extOrderItem->getDeliveryStatus();
		$default = array( 'email/delivery/' . $status . '/header-default.html', 'email/delivery/header-default.html' );

		return $view->render( $this->_getTemplate( $tplconf, $default ) );;
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return Client_Html_Interface Sub-client object
	 */
	public function getSubClient( $type, $name = null )
	{
		return $this->_createSubClient( 'email/delivery/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables.
	 */
	public function process()
	{
		$this->_process( $this->_subPartPath, $this->_subPartNames );
	}
}