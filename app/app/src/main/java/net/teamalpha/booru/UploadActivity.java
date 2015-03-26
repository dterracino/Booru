package net.teamalpha.booru;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.Uri;
import android.os.Bundle;
import android.preference.PreferenceManager;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;

public class UploadActivity extends Activity {

    EditText editTextSource;
    EditText editTextInfo;
    EditText editTextRating;
    EditText editTextTags;
    CheckBox checkBoxPrivate;
    Button buttonUpload;
    Uri imageURI;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_upload);
        editTextSource = (EditText) findViewById(R.id.editTextSource);
        editTextInfo = (EditText) findViewById(R.id.editTextInfo);
        editTextRating = (EditText) findViewById(R.id.editTextRating);
        editTextTags = (EditText) findViewById(R.id.editTextTags);
        checkBoxPrivate = (CheckBox) findViewById(R.id.checkBoxPrivate);
        buttonUpload = (Button) findViewById(R.id.buttonUpload);

        editTextRating.setText("7");
        final SharedPreferences prefs = PreferenceManager.getDefaultSharedPreferences(this);
        editTextTags.setText(prefs.getString(SettingsActivity.KEY_DEFAULT_TAGS, ""));

        buttonUpload.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                doUpload(prefs);
            }
        });

        Intent intent = getIntent();
        String type = intent.getType();
        if (intent.getAction().equals(Intent.ACTION_SEND) && type != null)
            if (type.startsWith("image/")) {
                imageURI = (Uri)intent.getParcelableExtra(Intent.EXTRA_STREAM);
            }

        if (imageURI == null) {
            final Activity activity = this;
            AlertDialog.Builder builder = new AlertDialog.Builder(this);
            builder.setMessage("No image URI inside intent");
            builder.setCancelable(false);
            builder.setNegativeButton("OK", new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialogInterface, int i) {
                    activity.finish();
                }
            });
            builder.create().show();
        }
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.menu_upload, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        int id = item.getItemId();
        if (id == R.id.action_settings) {
            Intent intent = new Intent(this, SettingsActivity.class);
            startActivity(intent);
            return true;
        }
        return super.onOptionsItemSelected(item);
    }

    private void doUpload(final SharedPreferences prefs) {
        UploadJob job = new UploadJob(
                prefs.getString(SettingsActivity.KEY_API_URL, null),
                prefs.getBoolean(SettingsActivity.KEY_IGNORE_CERT, false),
                prefs.getString(SettingsActivity.KEY_USERNAME, null),
                prefs.getString(SettingsActivity.KEY_PASSWORD, null),
                editTextSource.getText().toString(),
                editTextInfo.getText().toString(),
                Integer.parseInt(editTextRating.getText().toString()),
                checkBoxPrivate.isChecked(),
                editTextTags.getText().toString(),
                imageURI
        );
        UploadTask task = new UploadTask(this);
        task.execute(job);
    }
}
