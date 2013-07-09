<?php
/**
 * Description of en_mail_authorization_request
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
?>
<table>
    <tbody>
        <tr>
            <td>
                <p>Hi <?php echo $recipient_name; ?>!</p>
                <p>&nbsp;</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>
                    Welcome to Encrypted Twitter. $<?php echo $screenname."&nbsp;({$sender_name})"; ?> would like to authorize you. Get authorized now and visit the URL below.
                </p>
            </td>
        </tr>
        <tr>
            <td>
                <p>
                    <b>Authorization link</b>: <a href="<?php echo $link; ?>"><b><?php echo $link; ?></b></a>
                </p>
            </td>
        </tr>
        <tr>
            <td>
                <p>&nbsp;</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>
                    Don't forget to authorize your own friends!
                </p>
            </td>
        </tr>
        <tr>
            <td>
                <p>
                    Kind regards,
                </p>
                <p>
                    Encrypted Twitter Team
                </p>
            </td>
        </tr>
    </tbody>
</table>