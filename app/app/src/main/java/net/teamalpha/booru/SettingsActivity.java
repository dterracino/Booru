package net.teamalpha.booru;

import android.content.SharedPreferences;
import android.os.Bundle;
import android.preference.CheckBoxPreference;
import android.preference.EditTextPreference;
import android.preference.Preference;
import android.preference.PreferenceActivity;
import android.preference.PreferenceGroup;

public class SettingsActivity extends PreferenceActivity implements SharedPreferences.OnSharedPreferenceChangeListener {

    public static final String KEY_API_URL = "key_api_url";
    public static final String KEY_IGNORE_CERT = "key_ignore_cert";
    public static final String KEY_USERNAME = "key_username";
    public static final String KEY_PASSWORD = "key_password";
    public static final String KEY_DEFAULT_TAGS = "key_default_tags";

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        addPreferencesFromResource(R.xml.preferences);
        initSummary(getPreferenceScreen());
    }

    @Override
    public void onResume() {
        super.onResume();
        getPreferenceScreen().getSharedPreferences().registerOnSharedPreferenceChangeListener(this);
    }

    @Override
    public void onPause() {
        super.onPause();
        getPreferenceScreen().getSharedPreferences().unregisterOnSharedPreferenceChangeListener(this);
    }

    @Override
    public void onSharedPreferenceChanged(SharedPreferences sharedPreferences, String key) {
        Preference pref = findPreference(key);
        updatePrefSummary(pref);
    }

    private void updatePrefSummary(Preference pref) {
        if (pref.getKey().equals(KEY_PASSWORD)) {
        } else if (pref.getKey().equals(KEY_IGNORE_CERT)) {
            CheckBoxPreference chbPref = (CheckBoxPreference)pref;
            if (chbPref.isChecked()) {
                pref.setSummary(getString(R.string.pref_summary_cert_ignore));
            } else {
                pref.setSummary(getString(R.string.pref_summary_cert_check));
            }
        } else if (pref instanceof EditTextPreference) {
            EditTextPreference ePref = (EditTextPreference)pref;
            pref.setSummary(ePref.getText());
        } else {
            pref.setSummary(pref.toString());
        }
    }

    private void initSummary(Preference p) {
        if (p instanceof PreferenceGroup) {
            PreferenceGroup pGrp = (PreferenceGroup) p;
            for (int i = 0; i < pGrp.getPreferenceCount(); i++) {
                initSummary(pGrp.getPreference(i));
            }
        } else {
            updatePrefSummary(p);
        }
    }
}