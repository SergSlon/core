<div style="border: 1px solid #CCC; padding: 10px; background-color: #FFF; color: #333;">
	<p style="font-size: 13px; color: #333;"><strong><?php echo $severity; ?>!</strong></p>
	<p style="font-size: 13px; color: #333;"><?php echo $type; ?> [ <?php echo $severity; ?> ]: <?php echo $message; ?></p>
	<p style="font-size: 13px; color: #333;"><strong><?php echo $filePath; ?> @ line <?php echo $errorLine; ?>:</strong></p>
	<pre style="font-size: 13px; color: #333; border: 1px solid #EEE; padding: 3px 3px 3px 8px; margin: 0;overflow: auto;"><code><?php
if (is_array($debugLines)):
	echo ($errorLine - 1).":\t".trim($debugLines[$errorLine - 1])."\n";
	echo "<strong>{$errorLine}:\t".trim($debugLines[$errorLine])."</strong>\n";
	echo ($errorLine + 1).":\t".trim($debugLines[$errorLine + 1])."\n";
endif;
?></code></pre>
</div>