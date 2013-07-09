<?php
/**
 * Description of mail_verification_template
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
                <p>Hi <?php echo $name; ?>!</p>
                <p>&nbsp;</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>
                    Welcome to Encrypted Twitter. Please verify your e-mail address by clicking the link below. If you don't verify your e-mail address, you won't be able to login the next time!
                </p>
            </td>
        </tr>
        <tr>
            <td>
                <p>
                    <b>Verify link</b>: <a href="<?php echo $link; ?>"><b><?php echo $link; ?></b></a>
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
                    Thank you for joining us! Don't forget to invite all your friends!
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